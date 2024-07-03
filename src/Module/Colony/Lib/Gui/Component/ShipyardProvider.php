<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\BuildableRumpListItemInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShipyardProvider implements GuiComponentProviderInterface
{
    public function __construct(private BuildingFunctionRepositoryInterface $buildingFunctionRepository, private ShipRumpRepositoryInterface $shipRumpRepository, private ColonyLibFactoryInterface $colonyLibFactory)
    {
    }

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

        $user = $game->getUser();
        $userId = $user->getId();

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            array_map(
                fn (ShipRumpInterface $shipRump): BuildableRumpListItemInterface => $this->colonyLibFactory->createBuildableRumpItem(
                    $shipRump,
                    $user
                ),
                $this->shipRumpRepository->getBuildableByUserAndBuildingFunction($userId, $buildingFunction->getFunction())
            )
        );
        $game->setTemplateVar('BUILDABLE_RUMPS', $this->shipRumpRepository->getBuildableByUser($userId));
    }
}
