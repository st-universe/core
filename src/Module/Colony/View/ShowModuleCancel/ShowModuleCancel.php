<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleCancel;

use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowModuleCancel implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_CANCEL';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setTemplateVar('MODULE', ResourceCache()->getObject('module', request::postIntFatal('module')));
        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/queue_count');
        $game->setTemplateVar('COLONY', $colony);
    }
}
