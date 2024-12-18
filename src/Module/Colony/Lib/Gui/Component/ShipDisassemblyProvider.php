<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use RuntimeException;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShipDisassemblyProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private OrbitShipListRetrieverInterface $orbitShipListRetriever
    ) {}

    /** @param ColonyInterface $entity */
    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser(), false);

        $building = $field->getBuilding();
        if ($building === null) {
            throw new RuntimeException('building is null');
        }

        $fieldFunctions = $building->getFunctions()->toArray();
        $colonySurface = $this->colonyLibFactory->createColonySurface($entity);

        if ($colonySurface->hasShipyard()) {
            $repairableShips = [];
            foreach ($this->orbitShipListRetriever->retrieve($entity) as $fleet) {
                /** @var ShipInterface $ship */
                foreach ($fleet['ships'] as $ship) {
                    if ($ship->getUser() !== $game->getUser()) {
                        continue;
                    }
                    foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                        if (array_key_exists($rump_rel->getBuildingFunction()->value, $fieldFunctions)) {
                            $repairableShips[$ship->getId()] = $ship;
                            break;
                        }
                    }
                }
            }

            $game->setTemplateVar('SHIP_LIST', $repairableShips);
            $game->setTemplateVar('FIELD', $field);
        }
    }
}
