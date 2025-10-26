<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowWaste;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowWaste implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_WASTE';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyGuiHelperInterface $colonyGuiHelper)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_WASTE, $colony, $game);

        $game->showMacro(ColonyMenuEnum::MENU_WASTE->getTemplate());
    }
}
