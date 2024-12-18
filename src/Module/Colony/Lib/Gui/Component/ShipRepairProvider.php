<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use RuntimeException;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShipRepairProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository, private PlanetFieldHostProviderInterface $planetFieldHostProvider, private ColonyLibFactoryInterface $colonyLibFactory, private OrbitShipListRetrieverInterface $orbitShipListRetriever, private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory) {}

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
            $fleets = $this->orbitShipListRetriever->retrieve($entity);

            foreach ($fleets as $fleet) {
                $ships = array_filter($fleet['ships'], fn($ship) => $ship instanceof ShipInterface);

                foreach ($ships as $ship) {
                    $wrapper = $this->spacecraftWrapperFactory->wrapShip($ship);

                    if (
                        !$wrapper->canBeRepaired() || $ship->isUnderRepair()
                    ) {
                        continue;
                    }
                    foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                        if (array_key_exists($rump_rel->getBuildingFunction()->value, $fieldFunctions)) {
                            $repairableShips[$ship->getId()] = $wrapper;
                            break;
                        }
                    }
                }
            }

            $game->setTemplateVar('REPAIRABLE_SHIP_LIST', $repairableShips);
            $game->setTemplateVar('FIELD', $field);
        }
    }
}
