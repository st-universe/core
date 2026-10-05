<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use RuntimeException;
use Stu\Component\Colony\OrbitShipWrappersRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShipRetrofitProvider implements PlanetFieldHostComponentInterface
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

        $retrofitShipWrappers = [];
        $groups = $this->orbitShipWrappersRetriever->retrieve($entity);

        foreach ($groups as $group) {

            /** @var ShipWrapperInterface $wrapper */
            foreach ($group->getWrappers() as $wrapper) {

                $ship = $wrapper->get();
                if (
                    !$wrapper->canBeRetrofitted() || $ship->getCondition()->isUnderRetrofit()
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

        $template->setTemplateVar('RETROFIT_SHIP_WRAPPERS', $retrofitShipWrappers);
        $template->setTemplateVar('FIELD', $field);
    }
}
