<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowFighterShipyard;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowFighterShipyard implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_FIGHTER_SHIPYARD';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyGuiHelperInterface $colonyGuiHelper, private ShowFighterShipyardRequestInterface $showFighterShipyardRequest) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showFighterShipyardRequest->getColonyId(),
            $userId,
            false
        );

        $game->showMacro(ColonyMenuEnum::MENU_FIGHTER_SHIPYARD->getTemplate());

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_FIGHTER_SHIPYARD, $colony, $game);
    }
}
