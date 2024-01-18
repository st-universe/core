<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipDisassembly;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowShipDisassembly implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_DISASSEMBLY';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowShipDisassemblyRequestInterface $showShipDisassemblyRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowShipDisassemblyRequestInterface $showShipDisassemblyRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showShipDisassemblyRequest = $showShipDisassemblyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showShipDisassemblyRequest->getColonyId(),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_SHIP_DISASSEMBLY, $colony, $game);

        $game->showMacro(ColonyMenuEnum::MENU_SHIP_DISASSEMBLY->getTemplate());
    }
}
