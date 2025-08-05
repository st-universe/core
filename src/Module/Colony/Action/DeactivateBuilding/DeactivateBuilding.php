<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateBuilding;

use Override;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeactivateBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private BuildingActionInterface $buildingAction) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());
        $host = $field->getHost();

        $game->setView($host->getDefaultViewIdentifier());

        if ($field->isUnderConstruction()) {
            $field->setActivateAfterBuild(false);
            $game->getInfo()->addInformation("Gebäude wird nach Bau deaktiviert");
        } else {
            $this->buildingAction->deactivate(
                $field,
                $game
            );
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
