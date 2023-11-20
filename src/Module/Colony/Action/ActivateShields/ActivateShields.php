<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateShields;

use request;

use Stu\Component\Building\BuildingEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ActivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_SHIELDS';

    private ColonyLoaderInterface $colonyLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;


    private BuildingActionInterface $buildingAction;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingActionInterface $buildingAction
    ) {
        $this->colonyLoader = $colonyLoader;
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

        $this->buildingAction->activate(
            current($fields),
            $game
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
