<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuilding;

use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class ActivateBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE';

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
            $field->setActivateAfterBuild(true);
            $game->addInformation("Gebäude wird nach Bau aktiviert");
        } else {
            $this->buildingAction->activate(
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
