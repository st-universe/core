<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowAcademy implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ACADEMY';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyGuiHelperInterface $colonyGuiHelper, private ShowAcademyRequestInterface $showAcademyRequest) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showAcademyRequest->getColonyId(),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_ACADEMY, $colony, $game);

        $game->showMacro(ColonyMenuEnum::MENU_ACADEMY->getTemplate());
    }
}
