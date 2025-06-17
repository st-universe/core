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

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );
        $station = $wrapper->get();

        $cx = request::getIntFatal('x');
        $cy = request::getIntFatal('y');


        $field = $station->getLocation();
        $shipCx = $field->getCx();
        $shipCy = $field->getCy();

        if (
            $cx < $shipCx - $wrapper->getSensorRange()
            || $cx > $shipCx + $wrapper->getSensorRange()
            || $cy < $shipCy - $wrapper->getSensorRange()
            || $cy > $shipCy + $wrapper->getSensorRange()
        ) {
            return;
        }

        $mapField = $this->mapRepository->getByCoordinates($station->getLayer(), $cx, $cy);

        $game->showMacro('html/visualPanel/panel.twig');

        $system = $mapField->getSystem();
        if ($system === null) {
            return;
        }

        if ($mapField === null) {
            return;
        }

        if (!$station->getLss()) {
            return;
        }

        $game->setTemplateVar('VISUAL_PANEL', $this->stationUiFactory->createSystemScanPanel(
            $wrapper,
            $game->getUser(),
            $this->loggerUtilFactory->getLoggerUtil(),
            $system
        ));

        $game->setTemplateVar('SHIP', $station);
    }
}
