<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipRepair;

use Override;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowShipRepair implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyGuiHelperInterface $colonyGuiHelper, private ShowShipRepairRequestInterface $showShipRepairRequest)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showShipRepairRequest->getColonyId(),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_SHIP_REPAIR, $colony, $game);

        $game->showMacro(ColonyMenuEnum::MENU_SHIP_REPAIR->getTemplate());
    }
}
