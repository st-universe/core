<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleCancel;

use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ShowModuleCancel implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_CANCEL';

    private ColonyLoaderInterface $colonyLoader;

    private ShowModuleCancelRequestInterface $showModuleCancelRequest;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleCancelRequestInterface $showModuleCancelRequest,
        ModuleQueueRepositoryInterface $moduleQueueRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleCancelRequest = $showModuleCancelRequest;
        $this->moduleQueueRepository = $moduleQueueRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showModuleCancelRequest->getColonyId(),
            $userId,
            false
        );

        /** @var ModuleInterface $module */
        $module = $game->getViewContext(ViewContextTypeEnum::MODULE);

        $queuedAmount = $this->moduleQueueRepository->getAmountByColonyAndModule(
            $colony->getId(),
            $module->getId()
        );

        $game->showMacro('html/colonymacros.xhtml/queue_count');
        $game->setTemplateVar('MODULE', $module);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('MODULE_ID', $module->getId());
        $game->setTemplateVar('QUEUED_AMOUNT', $queuedAmount);
    }
}
