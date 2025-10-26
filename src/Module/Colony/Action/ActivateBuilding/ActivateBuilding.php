<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuilding;

use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class ActivateBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACTIVATE';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private BuildingActionInterface $buildingAction) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());
        $host = $field->getHost();

        $game->setView($host->getDefaultViewIdentifier());

        if ($field->isUnderConstruction()) {
            $field->setActivateAfterBuild(true);
            $game->getInfo()->addInformation("Gebäude wird nach Bau aktiviert");
        } else {
            $this->buildingAction->activate(
                $field,
                $game
            );
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
