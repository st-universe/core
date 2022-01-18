<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShuttleManagement;

use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowShuttleManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_SHUTTLE_MANAGEMENT';

    private ShowShuttleManagementRequestInterface $request;

    private ShipLoaderInterface $shipLoader;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShowShuttleManagementRequestInterface $request,
        ShipLoaderInterface $shipLoader,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->request = $request;
        $this->shipLoader = $shipLoader;
        $this->shipLoader = $shipLoader;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $stationId = $this->request->getStationId();
        $shipId = $this->request->getShipId();

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $stationId,
            $game->getUser()->getId(),
            $shipId
        );

        $station = $shipArray[$stationId];
        $ship = $shipArray[$shipId];

        $game->setPageTitle("Shuttle Management");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/stationmacros.xhtml/shuttlemanagement');

        $shuttles = [];
        $currentlyStored = 0;

        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $smi = new ShuttleManagementItem($stor->getCommodity());
                $smi->setCurrentLoad($stor->getAmount());
                $currentlyStored += $stor->getAmount();

                $this->loggerUtil->log(sprintf("currentLoad: %d", $smi->getCurrentLoad()));

                $shuttles[$stor->getCommodity()->getId()] = $smi;
            }
        }

        foreach ($station->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                if (array_key_exists($stor->getCommodity()->getId(), $shuttles)) {
                    $smi = $shuttles[$stor->getCommodity()->getId()];
                    $smi->setColonyLoad($stor->getAmount());
                } else {
                    $smi = new ShuttleManagementItem($stor->getCommodity());
                    $smi->setColonyLoad($stor->getAmount());

                    $shuttles[$stor->getCommodity()->getId()] = $smi;
                }
            }
        }

        $smi = current($shuttles);

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('STATION', $station);
        $game->setTemplateVar('CURRENTLY_STORED', $currentlyStored);
        $game->setTemplateVar('AVAILABLE_SHUTTLES', $shuttles);
        $game->setTemplateVar('ERROR', false);
    }
}
