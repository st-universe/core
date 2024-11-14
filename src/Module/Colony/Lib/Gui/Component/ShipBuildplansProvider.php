<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShipBuildplansProvider implements GuiComponentProviderInterface
{
    public function __construct(
        private BuildPlanDeleterInterface $buildPlanDeleter,
        private BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        private ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {}

    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
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
                fn(ShipBuildplanInterface $plan): array => [
                    'plan' => $plan,
                    'deletable' => $this->buildPlanDeleter->isDeletable($plan)
                ],
                $this->shipBuildplanRepository->getByUserAndBuildingFunction(
                    $game->getUser()->getId(),
                    $buildingFunction->getFunction()
                )
            )
        );
    }
}
