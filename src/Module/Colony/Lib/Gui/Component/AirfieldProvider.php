<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class AirfieldProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository) {}

    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {

        $game->setTemplateVar(
            'STARTABLE_SHIPS',
            $this->spacecraftRumpRepository->getStartableByColony($entity->getId())
        );
        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            $this->spacecraftRumpRepository->getBuildableByUserAndBuildingFunction(
                $game->getUser()->getId(),
                BuildingFunctionEnum::BUILDING_FUNCTION_AIRFIELD
            )
        );
    }
}
