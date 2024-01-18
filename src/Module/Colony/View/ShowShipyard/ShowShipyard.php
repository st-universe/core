<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipyard;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowShipyard implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPYARD';

    private ColonyLoaderInterface $colonyLoader;

    private ShowShipyardRequestInterface $showShipyardRequest;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipyardRequestInterface $showShipyardRequest,
        ColonyGuiHelperInterface $colonyGuiHelper,
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipyardRequest = $showShipyardRequest;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showShipyardRequest->getColonyId(),
            $userId,
            false
        );

        $game->showMacro(ColonyMenuEnum::MENU_SHIPYARD->getTemplate());

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_SHIPYARD, $colony, $game);
    }
}
