<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateBuilding;

use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeactivateBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private BuildingActionInterface $buildingAction;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        BuildingActionInterface $buildingAction
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->buildingAction = $buildingAction;
    }

    public function handle(GameControllerInterface $game): void
    {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());
        $host = $field->getHost();

        $game->setView($host->getDefaultViewIdentifier());

        if ($field->isUnderConstruction()) {
            $field->setActivateAfterBuild(false);
            $game->addInformation("Gebäude wird nach Bau deaktiviert");
        } else {
            $this->buildingAction->deactivate(
                $field,
                $game
            );
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
