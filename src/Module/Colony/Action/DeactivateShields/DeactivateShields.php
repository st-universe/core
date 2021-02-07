<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateShields;

use request;

use Stu\Component\Building\BuildingEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class DeactivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_SHIELDS';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;


    private BuildingActionInterface $buildingAction;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyRepositoryInterface $colonyRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingActionInterface $buildingAction
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyRepository = $colonyRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingAction = $buildingAction;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $colony = $this->colonyLoader->byIdAndUser(
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
            $colony,
            current($fields),
            $game
        );

        $colony->setShields(0);
        $this->colonyRepository->save($colony);

        $game->addInformation("Die Schilde wurden bei der Deaktivierung komplett entladen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
