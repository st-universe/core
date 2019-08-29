<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowTorpedoFab;

use ColonyMenu;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use TorpedoType;

final class ShowTorpedoFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TORPEDO_FAB';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showTorpedoFabRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowTorpedoFabRequestInterface $showTorpedoFabRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showTorpedoFabRequest = $showTorpedoFabRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showTorpedoFabRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/cm_torpedo_fab');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_TORPEDOFAB));
        $game->setTemplateVar('BUILDABLE_TORPEDO_TYPES', TorpedoType::getBuildableTorpedoTypesByUser($userId));
    }
}
