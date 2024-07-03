<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAirfield;

use Override;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowAirfield implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_AIRFIELD';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ShowAirfieldRequestInterface $showAirfieldRequest, private ColonyGuiHelperInterface $colonyGuiHelper)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showAirfieldRequest->getColonyId(),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_AIRFIELD, $colony, $game);

        $game->showMacro(ColonyMenuEnum::MENU_AIRFIELD->getTemplate());
    }
}
