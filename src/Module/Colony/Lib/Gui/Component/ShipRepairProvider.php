<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use RuntimeException;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShipRepairProvider implements GuiComponentProviderInterface
{
    private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    public function __construct(
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        ColonyLibFactoryInterface $colonyLibFactory,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
    }

    /** @param ColonyInterface $host */
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser(), false);

        $building = $field->getBuilding();
        if ($building === null) {
            throw new RuntimeException('building is null');
        }

        $fieldFunctions = $building->getFunctions()->toArray();
        $colonySurface = $this->colonyLibFactory->createColonySurface($host);

        if ($colonySurface->hasShipyard()) {
            $repairableShips = [];
            foreach ($this->orbitShipListRetriever->retrieve($host) as $fleet) {
                /** @var ShipInterface $ship */
                foreach ($fleet['ships'] as $ship) {
                    $wrapper = $this->shipWrapperFactory->wrapShip($ship);

                    if (
                        !$wrapper->canBeRepaired() || $ship->isUnderRepair()
                    ) {
                        continue;
                    }
                    foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                        if (array_key_exists($rump_rel->getBuildingFunction(), $fieldFunctions)) {
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
