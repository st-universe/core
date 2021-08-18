<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowSystemSensorScan;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use VisualNavPanel;

final class ShowSystemSensorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SYSTEM_SENSOR_SCAN';

    private ShipLoaderInterface $shipLoader;

    private MapRepositoryInterface $mapRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        MapRepositoryInterface $mapRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->mapRepository = $mapRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        if ($game->getUser()->getId() === 126) {
            $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
        } else {
            $this->loggerUtil->init();
        }

        $this->loggerUtil->log('hello!');

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
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

        $mapField = $this->mapRepository->getByCoordinates($cx, $cy);

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setMacro('html/stationmacros.xhtml/systemsensorscan');

        if ($mapField->getSystem() === null) {
            return;
        }

        if ($mapField === null) {
            return;
        }

        if (!$ship->getLss()) {
            return;
        }

        if ($ship->getLss()) {
            $game->setTemplateVar('VISUAL_NAV_PANEL', new VisualNavPanel(
                $ship,
                $game->getUser(),
                $this->loggerUtil,
                $ship->getTachyonState(),
                false,
                $mapField->getSystem()
            ));
        }

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('DONOTHING', false);
    }
}
