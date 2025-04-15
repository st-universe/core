<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShuttleManagement;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;

final class ShowShuttleManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHUTTLE_MANAGEMENT';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShowShuttleManagementRequestInterface $request,
        private StationLoaderInterface $stationLoader,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $stationId = $this->request->getStationId();
        $shipId = $this->request->getShipId();

        $wrappers = $this->stationLoader->getWrappersBySourceAndUserAndTarget(
            $stationId,
            $game->getUser()->getId(),
            $shipId,
            false,
            false
        );

        $wrapper = $wrappers->getSource();
        $station = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $ship = $targetWrapper->get();

        $game->setPageTitle("Shuttle Management");
        $game->setMacroInAjaxWindow('html/spacecraft/shuttleManagement.twig');

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

        $game->setTemplateVar('MODULE_VIEW', ModuleEnum::STATION);
        $game->setTemplateVar('WRAPPER', $this->spacecraftWrapperFactory->wrapSpacecraft($ship));
        $game->setTemplateVar('MANAGER', $station);
        $game->setTemplateVar('CURRENTLY_STORED', $currentlyStored);
        $game->setTemplateVar('AVAILABLE_SHUTTLES', $shuttles);
    }
}
