<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleCancel;

use ModuleQueue;
use ModulesData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowModuleCancel implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_CANCEL';

    private $colonyLoader;

    private $showModuleCancelRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleCancelRequestInterface $showModuleCancelRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleCancelRequest = $showModuleCancelRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleCancelRequest->getColonyId(),
            $userId
        );

        /**
         * @var ModulesData $module
         */
        $module = $game->getViewContext()['MODULE'];

        $queuedAmount = ModuleQueue::getAmountByColonyAndModule($colony->getId(), $module->getId());

        $game->showMacro('html/colonymacros.xhtml/queue_count');
        $game->setTemplateVar(
            'MODULE',
            ResourceCache()->getObject('module', $this->showModuleCancelRequest->getModuleId())
        );
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('MODULE_ID', $module->getId());
        $game->setTemplateVar('QUEUED_AMOUNT', $queuedAmount);
    }
}
