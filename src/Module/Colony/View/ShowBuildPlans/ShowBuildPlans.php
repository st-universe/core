<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowBuildPlans implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BUILDPLANS';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyGuiHelperInterface $colonyGuiHelper, private ShowBuildPlansRequestInterface $showBuildPlansRequest) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showBuildPlansRequest->getColonyId(),
            $userId,
            false
        );

        $game->showMacro(ColonyMenuEnum::MENU_BUILDPLANS->getTemplate());

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_BUILDPLANS, $colony, $game);
    }
}
