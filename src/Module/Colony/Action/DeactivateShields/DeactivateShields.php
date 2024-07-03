<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateShields;

use Override;
use request;

use Stu\Component\Building\BuildingEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class DeactivateShields implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_SHIELDS';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private PlanetFieldRepositoryInterface $planetFieldRepository, private BuildingActionInterface $buildingAction)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $fields = $this->planetFieldRepository->getByColonyAndBuildingFunction(
            $colony->getId(),
            [BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR]
        );

        if (count($fields) !== 1) {
            return;
        }

        $this->buildingAction->deactivate(
            current($fields),
            $game
        );

        $game->addInformation("Die Schilde wurden bei der Deaktivierung komplett entladen");
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
