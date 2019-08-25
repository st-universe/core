<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowTorpedoFab;

use ColonyMenu;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowTorpedoFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TORPEDO_FAB';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/cm_torpedo_fab');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_TORPEDOFAB));
    }
}
