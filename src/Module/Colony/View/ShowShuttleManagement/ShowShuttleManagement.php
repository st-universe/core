<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShuttleManagement;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowShuttleManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHUTTLE_MANAGEMENT';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShowShuttleManagementRequestInterface $request,
        private ColonyLoaderInterface $colonyLoader,
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private InteractionCheckerInterface $interactionChecker,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation($this->request->getColonyId(), $game->getUser()->getId());

        $ship = $this->shipRepository->find($this->request->getShipId());
        if ($ship === null) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($colony, $ship)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', null, self::VIEW_IDENTIFIER);
        }

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

        $game->setTemplateVar('MODULE_VIEW', ModuleEnum::COLONY);
        $game->setTemplateVar('WRAPPER', $this->spacecraftWrapperFactory->wrapShip($ship));
        $game->setTemplateVar('MANAGER', $colony);
        $game->setTemplateVar('CURRENTLY_STORED', $currentlyStored);
        $game->setTemplateVar('AVAILABLE_SHUTTLES', $shuttles);
    }
}
