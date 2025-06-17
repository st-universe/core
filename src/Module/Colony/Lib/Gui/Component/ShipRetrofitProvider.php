<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use RuntimeException;
use Stu\Component\Colony\OrbitShipWrappersRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShipRetrofitProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private OrbitShipWrappersRetrieverInterface $orbitShipWrappersRetriever
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

        if (!$colonySurface->hasShipyard()) {
            return;
        }

        $retrofitShipWrappers = [];
        $groups = $this->orbitShipWrappersRetriever->retrieve($entity);

        foreach ($groups as $group) {

            /** @var ShipWrapperInterface $wrapper */
            foreach ($group->getWrappers() as $wrapper) {

                $ship = $wrapper->get();
                if (
                    !$wrapper->canBeRetrofitted() || $ship->isUnderRetrofit()
                ) {
                    continue;
                }
                foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                    if (array_key_exists($rump_rel->getBuildingFunction()->value, $fieldFunctions)) {
                        $retrofitShipWrappers[$ship->getId()] = $wrapper;
                        break;
                    }
                }
            }
        }

        $game->setTemplateVar('RETROFIT_SHIP_WRAPPERS', $retrofitShipWrappers);
        $game->setTemplateVar('FIELD', $field);
    }
}
