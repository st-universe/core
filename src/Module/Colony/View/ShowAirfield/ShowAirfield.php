<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAirfield;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowAirfield implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_AIRFIELD';

    private ColonyLoaderInterface $colonyLoader;

    private ShowAirfieldRequestInterface $showAirfieldRequest;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowAirfieldRequestInterface $showAirfieldRequest,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showAirfieldRequest = $showAirfieldRequest;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showAirfieldRequest->getColonyId(),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_AIRFIELD, $colony, $game);

        $game->showMacro(ColonyMenuEnum::MENU_AIRFIELD->getTemplate());
    }
}
