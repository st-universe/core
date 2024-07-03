<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RemoveBuilding;

use Override;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class RemoveBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REMOVE_BUILDING';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private BuildingActionInterface $buildingAction)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowInformation::VIEW_IDENTIFIER);

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());

        if (!$field->hasBuilding()) {
            return;
        }
        if (!$field->getBuilding()->isRemovable()) {
            return;
        }

        $this->buildingAction->remove($field, $game);

        $game->addExecuteJS('refreshHost();');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
