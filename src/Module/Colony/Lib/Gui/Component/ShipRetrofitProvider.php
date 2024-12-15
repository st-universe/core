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

final class ShipRetrofitProvider implements GuiComponentProviderInterface
{
    public function __construct(private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository, private PlanetFieldHostProviderInterface $planetFieldHostProvider, private ColonyLibFactoryInterface $colonyLibFactory, private OrbitShipListRetrieverInterface $orbitShipListRetriever, private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory) {}

    /** @param ColonyInterface $host */
    #[Override]
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
            $retrofitShips = [];
            foreach ($this->orbitShipListRetriever->retrieve($host) as $fleet) {
                /** @var ShipInterface $ship */
                foreach ($fleet['ships'] as $ship) {
                    $wrapper = $this->spacecraftWrapperFactory->wrapShip($ship);

                    if (
                        !$wrapper->canBeRetrofitted() || $ship->isUnderRetrofit()
                    ) {
                        continue;
                    }
                    foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                        if (array_key_exists($rump_rel->getBuildingFunction()->value, $fieldFunctions)) {
                            $retrofitShips[$ship->getId()] = $wrapper;
                            break;
                        }
                    }
                }
            }

            $game->setTemplateVar('RETROFIT_SHIP_LIST', $retrofitShips);
            $game->setTemplateVar('FIELD', $field);
        }
    }
}
