<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Repair;

use Override;
use RuntimeException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Crew\CrewEnum;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

//TODO unit tests
final class RepairUtil implements RepairUtilInterface
{
    public function __construct(private ShipSystemRepositoryInterface $shipSystemRepository, private RepairTaskRepositoryInterface $repairTaskRepository, private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository, private ShipStorageManagerInterface $shipStorageManager, private ColonyStorageManagerInterface $colonyStorageManager, private ColonyFunctionManagerInterface $colonyFunctionManager, private PrivateMessageSenderInterface $privateMessageSender) {}

    //REPAIR STUFF
    #[Override]
    public function determineSpareParts(ShipWrapperInterface $wrapper, bool $tickBased): array
    {
        $isRepairStationBonus = $this->isRepairStationBonus($wrapper);

        $neededSpareParts = $this->calculateNeededSpareParts($wrapper, $isRepairStationBonus, $tickBased);
        $neededSystemComponents = $this->calculateNeededSystemComponents($wrapper, $isRepairStationBonus, $tickBased);

        return [
            CommodityTypeEnum::COMMODITY_SPARE_PART => $neededSpareParts,
            CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT => $neededSystemComponents
        ];
    }

    private function calculateNeededSpareParts(ShipWrapperInterface $wrapper, bool $isRepairStationBonus, bool $tickBased): int
    {
        $neededSpareParts = 0;
        $ship = $wrapper->get();
        $hull = $ship->getHull();
        $maxHull = $ship->getMaxHull();

        if ($hull < $maxHull) {
            if ($tickBased) {
                $neededSpareParts += 1;
            } else {
                $hullRepairParts = ($maxHull - $hull) / RepairTaskEnum::HULL_HITPOINTS_PER_SPARE_PART;
                if ($isRepairStationBonus) {
                    $neededSpareParts += (int)ceil($hullRepairParts / 2);
                } else {
                    $neededSpareParts += (int)ceil($hullRepairParts);
                }
            }
        }

        $damagedSystems = $wrapper->getDamagedSystems();
        $maxSystems = $tickBased ? ($isRepairStationBonus ? 4 : 2) : count($damagedSystems);
        $systemCount = min(count($damagedSystems), $maxSystems);

        for ($i = 0; $i < $systemCount; $i++) {
            $system = $damagedSystems[$i];
            $systemLvl = $system->determineSystemLevel();
            $healingPercentage = (100 - $system->getStatus()) / 100;
            $systemRepairParts = $healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskEnum::SPARE_PARTS_ONLY];
            if ($isRepairStationBonus) {
                $neededSpareParts += (int)ceil($systemRepairParts / 2);
            } else {
                $neededSpareParts += (int)ceil($systemRepairParts);
            }
        }

        return $neededSpareParts;
    }

    private function calculateNeededSystemComponents(ShipWrapperInterface $wrapper, bool $isRepairStationBonus, bool $tickBased): int
    {
        $neededSystemComponents = 0;
        $damagedSystems = $wrapper->getDamagedSystems();
        $maxSystems = $tickBased ? ($isRepairStationBonus ? 4 : 2) : count($damagedSystems);
        $systemCount = min(count($damagedSystems), $maxSystems);

        for ($i = 0; $i < $systemCount; $i++) {
            $system = $damagedSystems[$i];
            $systemLvl = $system->determineSystemLevel();
            $healingPercentage = (100 - $system->getStatus()) / 100;
            $systemComponents = $healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskEnum::SYSTEM_COMPONENTS_ONLY];
            if ($isRepairStationBonus) {
                $neededSystemComponents += (int)ceil($systemComponents / 2);
            } else {
                $neededSystemComponents += (int)ceil($systemComponents);
            }
        }

        return $neededSystemComponents;
    }

    #[Override]
    public function enoughSparePartsOnEntity(array $neededParts, ColonyInterface|ShipInterface $entity, ShipInterface $ship): bool
    {
        $neededSpareParts = $neededParts[CommodityTypeEnum::COMMODITY_SPARE_PART];
        $neededSystemComponents = $neededParts[CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT];

        if ($neededSpareParts > 0) {
            $spareParts = $entity->getStorage()->get(CommodityTypeEnum::COMMODITY_SPARE_PART);

            if ($spareParts === null || $spareParts->getAmount() < $neededSpareParts) {
                $this->sendNeededAmountMessage($neededSpareParts, $neededSystemComponents, $ship, $entity);
                return false;
            }
        }

        if ($neededSystemComponents > 0) {
            $systemComponents = $entity->getStorage()->get(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT);

            if ($systemComponents === null || $systemComponents->getAmount() < $neededSystemComponents) {
                $this->sendNeededAmountMessage($neededSpareParts, $neededSystemComponents, $ship, $entity);
                return false;
            }
        }

        return true;
    }

    private function sendNeededAmountMessage(
        int $neededSpareParts,
        int $neededSystemComponents,
        ShipInterface $ship,
        ColonyInterface|ShipInterface $entity
    ): void {
        $neededPartsString = sprintf(
            "%d %s%s",
            $neededSpareParts,
            CommodityTypeEnum::getDescription(CommodityTypeEnum::COMMODITY_SPARE_PART),
            ($neededSystemComponents > 0 ? sprintf(
                "\n%d %s",
                $neededSystemComponents,
                CommodityTypeEnum::getDescription(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT)
            ) : '')
        );

        $isColony = $entity instanceof ColonyInterface;

        //PASSIVE REPAIR OF STATION BY WORKBEES
        if ($entity === $ship) {
            $entityOwnerMessage = sprintf(
                "Die Reparatur der %s %s wurde in Sektor %s angehalten.\nEs werden folgende Waren benötigt:\n%s",
                $entity->getRump()->getName(),
                $ship->getName(),
                $ship->getSectorString(),
                $neededPartsString
            );
        } else {
            $entityOwnerMessage = $isColony ? sprintf(
                "Die Reparatur der %s von Siedler %s wurde in Sektor %s bei der Kolonie %s angehalten.\nEs werden folgende Waren benötigt:\n%s",
                $ship->getName(),
                $ship->getUser()->getName(),
                $ship->getSectorString(),
                $entity->getName(),
                $neededPartsString
            ) : sprintf(
                "Die Reparatur der %s von Siedler %s wurde in Sektor %s bei der %s %s angehalten.\nEs werden folgende Waren benötigt:\n%s",
                $ship->getName(),
                $ship->getUser()->getName(),
                $ship->getSectorString(),
                $entity->getRump()->getName(),
                $entity->getName(),
                $neededPartsString
            );
        }
        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $entity->getUser()->getId(),
            $entityOwnerMessage,
            $isColony ? PrivateMessageFolderTypeEnum::SPECIAL_COLONY : PrivateMessageFolderTypeEnum::SPECIAL_STATION
        );
    }

    #[Override]
    public function consumeSpareParts(array $neededParts, ColonyInterface|ShipInterface $entity): void
    {
        foreach ($neededParts as $commodityKey => $amount) {
            //$this->loggerUtil->log(sprintf('consume, cid: %d, amount: %d', $commodityKey, $amount));

            if ($amount < 1) {
                continue;
            }

            $storage = $entity->getStorage()->get($commodityKey);
            if ($storage === null) {
                throw new RuntimeException('enoughSparePartsOnEntity should be called beforehand!');
            }
            $commodity = $storage->getCommodity();

            if ($entity instanceof ColonyInterface) {
                $this->colonyStorageManager->lowerStorage($entity, $commodity, $amount);
            } else {
                $this->shipStorageManager->lowerStorage($entity, $commodity, $amount);
            }
        }
    }


    //SELFREPAIR STUFF

    #[Override]
    public function determineFreeEngineerCount(ShipInterface $ship): int
    {
        $engineerCount = 0;

        $engineerOptions = [];
        $nextNumber = 1;
        foreach ($ship->getCrewAssignments() as $shipCrew) {
            if (
                $shipCrew->getSlot() === CrewEnum::CREW_TYPE_TECHNICAL
                //&& $shipCrew->getRepairTask() === null
            ) {
                $engineerOptions[] = $nextNumber;
                $nextNumber++;
                $engineerCount++;
            }
        }

        return $engineerCount; //$engineerOptions;
    }

    #[Override]
    public function determineRepairOptions(ShipWrapperInterface $wrapper): array
    {
        $repairOptions = [];

        $ship = $wrapper->get();

        //check for hull option
        $hullPercentage = (int) ($ship->getHull() * 100 / $ship->getMaxHull());
        if ($hullPercentage < RepairTaskEnum::BOTH_MAX) {
            $hullSystem = $this->shipSystemRepository->prototype();
            $hullSystem->setSystemType(ShipSystemTypeEnum::SYSTEM_HULL);
            $hullSystem->setStatus($hullPercentage);

            $repairOptions[ShipSystemTypeEnum::SYSTEM_HULL->value] = $hullSystem;
        }

        //check for system options
        foreach ($wrapper->getDamagedSystems() as $system) {
            if ($system->getStatus() < RepairTaskEnum::BOTH_MAX) {
                $repairOptions[$system->getSystemType()->value] = $system;
            }
        }

        return $repairOptions;
    }

    #[Override]
    public function createRepairTask(ShipInterface $ship, ShipSystemTypeEnum $systemType, int $repairType, int $finishTime): void
    {
        $obj = $this->repairTaskRepository->prototype();

        $obj->setUser($ship->getUser());
        $obj->setShip($ship);
        $obj->setSystemType($systemType);
        $obj->setHealingPercentage($this->determineHealingPercentage($repairType));
        $obj->setFinishTime($finishTime);

        $this->repairTaskRepository->save($obj);
    }

    #[Override]
    public function determineHealingPercentage(int $repairType): int
    {
        $percentage = 0;

        if ($repairType === RepairTaskEnum::SPARE_PARTS_ONLY) {
            $percentage += random_int(RepairTaskEnum::SPARE_PARTS_ONLY_MIN, RepairTaskEnum::SPARE_PARTS_ONLY_MAX);
        } elseif ($repairType === RepairTaskEnum::SYSTEM_COMPONENTS_ONLY) {
            $percentage += random_int(RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MIN, RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MAX);
        } elseif ($repairType === RepairTaskEnum::BOTH) {
            $percentage += random_int(RepairTaskEnum::BOTH_MIN, RepairTaskEnum::BOTH_MAX);
        }

        return $percentage;
    }

    #[Override]
    public function instantSelfRepair(ShipInterface $ship, ShipSystemTypeEnum $systemType, int $healingPercentage): bool
    {
        return $this->internalSelfRepair(
            $ship,
            $systemType,
            $healingPercentage
        );
    }

    #[Override]
    public function selfRepair(ShipInterface $ship, RepairTaskInterface $repairTask): bool
    {
        $systemType = $repairTask->getSystemType();
        $percentage = $repairTask->getHealingPercentage();

        $this->repairTaskRepository->delete($repairTask);

        return $this->internalSelfRepair($ship, $systemType, $percentage);
    }

    private function internalSelfRepair(ShipInterface $ship, ShipSystemTypeEnum $systemType, int $percentage): bool
    {
        $result = true;

        if ($systemType === ShipSystemTypeEnum::SYSTEM_HULL) {
            $hullPercentage = (int) ($ship->getHull() * 100 / $ship->getMaxHull());

            if ($hullPercentage > $percentage) {
                $result = false;
            } else {
                $ship->setHuell((int)($ship->getMaxHull() * $percentage / 100));
            }
        } else {
            $system = $ship->getShipSystem($systemType);

            if ($system->getStatus() > $percentage) {
                $result = false;
            } else {
                $system->setStatus($percentage);
                $this->shipSystemRepository->save($system);
            }
        }

        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        return $result;
    }

    #[Override]
    public function isRepairStationBonus(ShipWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();

        $colony = $ship->isOverColony();
        if ($colony === null) {
            return false;
        }

        return $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);
    }

    #[Override]
    public function getRepairDuration(ShipWrapperInterface $wrapper): int
    {
        $ship = $wrapper->get();
        $ticks = $this->getRepairTicks($wrapper);

        //check if repair station is active
        $colonyRepair = $this->colonyShipRepairRepository->getByShip($ship->getId());
        if ($colonyRepair !== null) {
            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colonyRepair->getColony(), BuildingFunctionEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);
            if ($isRepairStationBonus) {
                $ticks = (int)ceil($ticks / 2);
            }
        }

        return $ticks;
    }

    #[Override]
    public function getRepairDurationPreview(ShipWrapperInterface $wrapper): int
    {
        $ship = $wrapper->get();
        $ticks = $this->getRepairTicks($wrapper);

        $colony = $ship->isOverColony();
        if ($colony !== null) {
            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);
            if ($isRepairStationBonus) {
                $ticks = (int)ceil($ticks / 2);
            }
        }

        return $ticks;
    }

    private function getRepairTicks(ShipWrapperInterface $wrapper): int
    {
        $ship = $wrapper->get();
        $ticks = (int) ceil(($ship->getMaxHull() - $ship->getHull()) / $ship->getRepairRate());

        return max($ticks, (int) ceil(count($wrapper->getDamagedSystems()) / 2));
    }
}
