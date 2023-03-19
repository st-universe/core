<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShuttleManagement;

use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowShuttleManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHUTTLE_MANAGEMENT';

    private ShowShuttleManagementRequestInterface $request;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShowShuttleManagementRequestInterface $request,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->request = $request;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipRepository->find($this->request->getShipId());
        $colony = $this->colonyRepository->find($this->request->getColonyId());

        if ($game->getUser() !== $colony->getUser()) {
            return;
        }

        $game->setPageTitle("Shuttle Management");
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/shuttlemanagement');

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

        foreach ($colony->getStorage() as $stor) {
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

        $game->setTemplateVar('WRAPPER', $this->shipWrapperFactory->wrapShip($ship));
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('CURRENTLY_STORED', $currentlyStored);
        $game->setTemplateVar('AVAILABLE_SHUTTLES', $shuttles);
        $game->setTemplateVar('ERROR', false);
    }
}
