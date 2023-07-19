<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowSystemSensorScan;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Ui\ShipUiFactoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowSystemSensorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SYSTEM_SENSOR_SCAN';

    private ShipLoaderInterface $shipLoader;

    private MapRepositoryInterface $mapRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    private ShipUiFactoryInterface $shipUiFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        MapRepositoryInterface $mapRepository,
        ShipUiFactoryInterface $shipUiFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->mapRepository = $mapRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
        $this->shipUiFactory = $shipUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );

        $cx = request::getIntFatal('cx');
        $cy = request::getIntFatal('cy');

        $game->setTemplateVar('DONOTHING', true);

        if (
            $cx < $ship->getCx() - $ship->getSensorRange()
            || $cx > $ship->getCx() + $ship->getSensorRange()
            || $cy < $ship->getCy() - $ship->getSensorRange()
            || $cy > $ship->getCy() + $ship->getSensorRange()
        ) {
            return;
        }

        $mapField = $this->mapRepository->getByCoordinates($ship->getLayerId(), $cx, $cy);

        $game->showMacro('html/stationmacros.xhtml/systemsensorscan');

        if ($mapField->getSystem() === null) {
            return;
        }

        if ($mapField === null) {
            return;
        }

        if (!$ship->getLss()) {
            return;
        }

        $game->setTemplateVar('VISUAL_NAV_PANEL', $this->shipUiFactory->createVisualNavPanel(
            $ship,
            $game->getUser(),
            $this->loggerUtilFactory->getLoggerUtil(),
            $ship->getTachyonState(),
            false,
            $mapField->getSystem()
        ));

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('DONOTHING', false);
    }
}
