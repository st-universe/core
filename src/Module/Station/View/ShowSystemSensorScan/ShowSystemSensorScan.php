<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowSystemSensorScan;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Station\Lib\StationUiFactoryInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowSystemSensorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SYSTEM_SENSOR_SCAN';

    private ShipLoaderInterface $shipLoader;

    private MapRepositoryInterface $mapRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    private StationUiFactoryInterface $stationUiFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        MapRepositoryInterface $mapRepository,
        StationUiFactoryInterface $stationUiFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->mapRepository = $mapRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
        $this->stationUiFactory = $stationUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $cx = request::getIntFatal('cx');
        $cy = request::getIntFatal('cy');


        $field = $ship->getCurrentMapField();
        if ($field instanceof MapInterface) {
            $shipCx = $field->getCx();
            $shipCy = $field->getCx();
        } else {
            $shipCx = $field->getSystem()->getCx();
            $shipCy = $field->getSystem()->getCy();
        }

        if (
            $cx < $shipCx - $ship->getSensorRange()
            || $cx > $shipCx + $ship->getSensorRange()
            || $cy < $shipCy - $ship->getSensorRange()
            || $cy > $shipCy + $ship->getSensorRange()
        ) {
            return;
        }

        $mapField = $this->mapRepository->getByCoordinates($ship->getLayer(), $cx, $cy);

        $game->showMacro('html/visualPanel/panel.twig');

        $system = $mapField->getSystem();
        if ($system === null) {
            return;
        }

        if ($mapField === null) {
            return;
        }

        if (!$ship->getLss()) {
            return;
        }

        $game->setTemplateVar('VISUAL_PANEL', $this->stationUiFactory->createSystemScanPanel(
            $ship,
            $game->getUser(),
            $this->loggerUtilFactory->getLoggerUtil(),
            $system
        ));

        $game->setTemplateVar('SHIP', $ship);
    }
}
