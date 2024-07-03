<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipyard;

use Override;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowShipyard implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPYARD';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ShowShipyardRequestInterface $showShipyardRequest, private ColonyGuiHelperInterface $colonyGuiHelper)
    {
    }

    #[Override]
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
