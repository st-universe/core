<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class SpacecraftBuildplansProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private BuildPlanDeleterInterface $buildPlanDeleter,
        private BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository
    ) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {

        $buildingFunction = $this->buildingFunctionRepository->find(
            request::getIntFatal('func')
        );

        if ($buildingFunction === null) {
            return;
        }

        $game->setTemplateVar(
            'AVAILABLE_BUILDPLANS',
            array_map(
                fn(SpacecraftBuildplan $plan): array => [
                    'plan' => $plan,
                    'deletable' => $this->buildPlanDeleter->isDeletable($plan)
                ],
                $this->spacecraftBuildplanRepository->getByUserAndBuildingFunction(
                    $game->getUser()->getId(),
                    $buildingFunction->getFunction()
                )
            )
        );
    }
}
