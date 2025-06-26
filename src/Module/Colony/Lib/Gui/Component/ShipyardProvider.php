<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\BuildableRumpListItemInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class ShipyardProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private ColonyLibFactoryInterface $colonyLibFactory
    ) {}

    #[Override]
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

        $user = $game->getUser();
        $userId = $user->getId();

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            array_map(
                fn(SpacecraftRump $shipRump): BuildableRumpListItemInterface => $this->colonyLibFactory->createBuildableRumpItem(
                    $shipRump,
                    $user
                ),
                $this->spacecraftRumpRepository->getBuildableByUserAndBuildingFunction($userId, $buildingFunction->getFunction())
            )
        );
        $game->setTemplateVar('BUILDABLE_RUMPS', $this->spacecraftRumpRepository->getBuildableByUser($userId));
    }
}
