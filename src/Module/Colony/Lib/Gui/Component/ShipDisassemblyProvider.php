<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use RuntimeException;
use Stu\Component\Colony\OrbitShipWrappersRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShipDisassemblyProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private OrbitShipWrappersRetrieverInterface $orbitShipWrappersRetriever
    ) {}

    /** @param Colony $entity */
    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($user, false);

        $building = $field->getBuilding();
        if ($building === null) {
            throw new RuntimeException('building is null');
        }

        $fieldFunctions = $building->getFunctions()->toArray();
        $colonySurface = $this->colonyLibFactory->createColonySurface($entity);

        if (!$colonySurface->hasShipyard()) {
            return;
        }

        $repairableShips = [];
        foreach ($this->orbitShipWrappersRetriever->retrieve($entity) as $group) {

            foreach ($group->getWrappers() as $wrapper) {
                $ship = $wrapper->get();
                if ($ship->getUser()->getId() !== $user->getId()) {
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

        $template->setTemplateVar('SHIP_LIST', $repairableShips);
        $template->setTemplateVar('FIELD', $field);
    }
}
