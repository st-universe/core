<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSpecialResearch;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class ShowSpecialResearch implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SPECIAL_RESEARCH';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyGuiHelperInterface $gui) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(request::indInt('id'), $game->getUser()->getId(), false);
        $this->gui->registerMenuComponents(ColonyMenuEnum::MENU_SPECIAL_RESEARCH, $colony, $game);
        $game->showMacro(ColonyMenuEnum::MENU_SPECIAL_RESEARCH->getTemplate());
    }
}
