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
    public const VIEW_IDENTIFIER = 'SHOW_BUILDPLANS';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowBuildPlansRequestInterface $showBuildPlansRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowBuildPlansRequestInterface $showBuildPlansRequest,
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showBuildPlansRequest = $showBuildPlansRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildPlansRequest->getColonyId(),
            $userId,
            false
        );

        $game->showMacro(ColonyMenuEnum::MENU_BUILDPLANS->getTemplate());

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_BUILDPLANS, $colony, $game);
    }
}
