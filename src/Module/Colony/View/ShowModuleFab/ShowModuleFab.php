<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use ColonyMenu;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowModuleFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULEFAB';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showModuleFabRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowModuleFabRequestInterface $showModuleFabRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showModuleFabRequest = $showModuleFabRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleFabRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/cm_modulefab');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_MODULEFAB));
    }
}
