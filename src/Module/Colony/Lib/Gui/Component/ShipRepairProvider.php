<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui\Component;

use RuntimeException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\OrbitShipWrappersRetrieverInterface;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\PassiveRepairPreviewWrapper;
use Stu\Module\Spacecraft\Lib\ShipRepairCost;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

use Stu\Module\Commodity\CommodityTypeConstants;

final class ShipRepairProvider implements PlanetFieldHostComponentInterface
{
    private readonly ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;
    private readonly PlanetFieldHostProviderInterface $planetFieldHostProvider;
    private readonly ColonyFunctionManagerInterface $colonyFunctionManager;
    private readonly OrbitShipWrappersRetrieverInterface $orbitShipWrappersRetriever;
    private readonly RepairUtilInterface $repairUtil;

    public function __construct(
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        OrbitShipWrappersRetrieverInterface $orbitShipWrappersRetriever,
        RepairUtilInterface $repairUtil
    ) {
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->orbitShipWrappersRetriever = $orbitShipWrappersRetriever;
        $this->repairUtil = $repairUtil;
    }

    /** @param Colony $entity */
    #[\Override]
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
        $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($entity, BuildingFunctionEnum::REPAIR_SHIPYARD);

        $repairableShipWrappers = [];
        foreach ($this->orbitShipWrappersRetriever->retrieve($entity) as $group) {
            foreach ($group->getWrappers() as $wrapper) {
                $ship = $wrapper->get();
                if (!$wrapper->canBeRepaired() || $ship->getCondition()->isUnderRepair()) {
                    continue;
                }

                foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rumpRelation) {
                    if (!array_key_exists($rumpRelation->getBuildingFunction()->value, $fieldFunctions)) {
                        continue;
                    }

                    $repairCosts = $this->repairUtil->determinePassiveRepairSpareParts(
                        $wrapper,
                        $isRepairStationBonus,
                        false
                    );

                    $repairableShipWrappers[$ship->getId()] = new PassiveRepairPreviewWrapper(
                        $wrapper,
                        $this->repairUtil->getPassiveRepairEstimatedDuration($wrapper, $isRepairStationBonus),
                        [
                            new ShipRepairCost(
                                $repairCosts[CommodityTypeConstants::COMMODITY_SPARE_PART],
                                CommodityTypeConstants::COMMODITY_SPARE_PART,
                                CommodityTypeConstants::getDescription(CommodityTypeConstants::COMMODITY_SPARE_PART)
                            ),
                            new ShipRepairCost(
                                $repairCosts[CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT],
                                CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT,
                                CommodityTypeConstants::getDescription(CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT)
                            )
                        ]
                    );
                    break;
                }
            }
        }

        $game->setTemplateVar('REPAIRABLE_SHIP_WRAPPERS', $repairableShipWrappers);
        $game->setTemplateVar('FIELD', $field);
    }
}
