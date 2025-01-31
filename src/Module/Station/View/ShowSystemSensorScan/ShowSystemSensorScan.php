<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowSystemSensorScan;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\Lib\StationUiFactoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowSystemSensorScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SYSTEM_SENSOR_SCAN';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private MapRepositoryInterface $mapRepository,
        private StationUiFactoryInterface $stationUiFactory,
        private LoggerUtilFactoryInterface $loggerUtilFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $cx = request::getIntFatal('x');
        $cy = request::getIntFatal('y');


        $field = $ship->getLocation();
        $shipCx = $field->getCx();
        $shipCy = $field->getCy();

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
