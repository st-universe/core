<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

use Override;
use RuntimeException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

//TODO unit tests
final class RepairUtil implements RepairUtilInterface
{
    public const int REPAIR_RATE_PER_TICK = 100;

    public function __construct(
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private RepairTaskRepositoryInterface $repairTaskRepository,
        private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private StorageManagerInterface $storageManager,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    //REPAIR STUFF
    #[Override]
    public function determineSpareParts(SpacecraftWrapperInterface $wrapper, bool $tickBased): array
    {
        $isRepairStationBonus = $this->isRepairStationBonus($wrapper);

        $neededSpareParts = $this->calculateNeededSpareParts($wrapper, $isRepairStationBonus, $tickBased);
        $neededSystemComponents = $this->calculateNeededSystemComponents($wrapper, $isRepairStationBonus, $tickBased);

        return [
            CommodityTypeConstants::COMMODITY_SPARE_PART => $neededSpareParts,
            CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT => $neededSystemComponents
        ];
    }

    private function calculateNeededSpareParts(SpacecraftWrapperInterface $wrapper, bool $isRepairStationBonus, bool $tickBased): int
    {
        $neededSpareParts = 0;
        $ship = $wrapper->get();
        $hull = $ship->getCondition()->getHull();
        $maxHull = $ship->getMaxHull();

        if ($hull < $maxHull) {
            if ($tickBased) {
                $neededSpareParts += 1;
            } else {
                $hullRepairParts = ($maxHull - $hull) / RepairTaskConstants::HULL_HITPOINTS_PER_SPARE_PART;
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
            $systemRepairParts = $healingPercentage * RepairTaskConstants::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskConstants::SPARE_PARTS_ONLY];
            if ($isRepairStationBonus) {
                $neededSpareParts += (int)ceil($systemRepairParts / 2);
            } else {
                $neededSpareParts += (int)ceil($systemRepairParts);
            }
        }

        return $neededSpareParts;
    }

    private function calculateNeededSystemComponents(SpacecraftWrapperInterface $wrapper, bool $isRepairStationBonus, bool $tickBased): int
    {
        $neededSystemComponents = 0;
        $damagedSystems = $wrapper->getDamagedSystems();
        $maxSystems = $tickBased ? ($isRepairStationBonus ? 4 : 2) : count($damagedSystems);
        $systemCount = min(count($damagedSystems), $maxSystems);

        for ($i = 0; $i < $systemCount; $i++) {
            $system = $damagedSystems[$i];
            $systemLvl = $system->determineSystemLevel();
            $healingPercentage = (100 - $system->getStatus()) / 100;
            $systemComponents = $healingPercentage * RepairTaskConstants::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskConstants::SYSTEM_COMPONENTS_ONLY];
            if ($isRepairStationBonus) {
                $neededSystemComponents += (int)ceil($systemComponents / 2);
            } else {
                $neededSystemComponents += (int)ceil($systemComponents);
            }
        }

        return $neededSystemComponents;
    }

    #[Override]
    public function enoughSparePartsOnEntity(
        array $neededParts,
        Colony|Spacecraft $entity,
        Spacecraft $spacecraft
    ): bool {
        $neededSpareParts = $neededParts[CommodityTypeConstants::COMMODITY_SPARE_PART];
        $neededSystemComponents = $neededParts[CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT];

        if ($neededSpareParts > 0) {
            $spareParts = $entity->getStorage()->get(CommodityTypeConstants::COMMODITY_SPARE_PART);

            if ($spareParts === null || $spareParts->getAmount() < $neededSpareParts) {
                $this->sendNeededAmountMessage($neededSpareParts, $neededSystemComponents, $spacecraft, $entity);
                return false;
            }
        }

        if ($neededSystemComponents > 0) {
            $systemComponents = $entity->getStorage()->get(CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT);

            if ($systemComponents === null || $systemComponents->getAmount() < $neededSystemComponents) {
                $this->sendNeededAmountMessage($neededSpareParts, $neededSystemComponents, $spacecraft, $entity);
                return false;
            }
        }

        return true;
    }

    private function sendNeededAmountMessage(
        int $neededSpareParts,
        int $neededSystemComponents,
        Spacecraft $spacecraft,
        Colony|Spacecraft $entity
    ): void {
        $neededPartsString = sprintf(
            "%d %s%s",
            $neededSpareParts,
            CommodityTypeConstants::getDescription(CommodityTypeConstants::COMMODITY_SPARE_PART),
            ($neededSystemComponents > 0 ? sprintf(
                "\n%d %s",
                $neededSystemComponents,
                CommodityTypeConstants::getDescription(CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT)
            ) : '')
        );

        $isColony = $entity instanceof Colony;

        //PASSIVE REPAIR OF STATION BY WORKBEES
        if ($entity === $spacecraft) {
            $entityOwnerMessage = sprintf(
                "Die Reparatur der %s %s wurde in Sektor %s angehalten.\nEs werden folgende Waren benötigt:\n%s",
                $entity->getRump()->getName(),
                $spacecraft->getName(),
                $spacecraft->getSectorString(),
                $neededPartsString
            );
        } else {
            $entityOwnerMessage = $isColony ? sprintf(
                "Die Reparatur der %s von Siedler %s wurde in Sektor %s bei der Kolonie %s angehalten.\nEs werden folgende Waren benötigt:\n%s",
                $spacecraft->getName(),
                $spacecraft->getUser()->getName(),
                $spacecraft->getSectorString(),
                $entity->getName(),
                $neededPartsString
            ) : sprintf(
                "Die Reparatur der %s von Siedler %s wurde in Sektor %s bei der %s %s angehalten.\nEs werden folgende Waren benötigt:\n%s",
                $spacecraft->getName(),
                $spacecraft->getUser()->getName(),
                $spacecraft->getSectorString(),
                $entity->getRump()->getName(),
                $entity->getName(),
                $neededPartsString
            );
        }
        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $entity->getUser()->getId(),
            $entityOwnerMessage,
            $isColony ? PrivateMessageFolderTypeEnum::SPECIAL_COLONY : PrivateMessageFolderTypeEnum::SPECIAL_STATION
        );
    }

    #[Override]
    public function consumeSpareParts(array $neededParts, Colony|Spacecraft $entity): void
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
            $this->storageManager->lowerStorage($entity, $commodity, $amount);
        }
    }


    //SELFREPAIR STUFF

    #[Override]
    public function determineFreeEngineerCount(Spacecraft $ship): int
    {
        $engineerCount = 0;

        $engineerOptions = [];
        $nextNumber = 1;
        foreach ($ship->getCrewAssignments() as $shipCrew) {
            if (
                $shipCrew->getSlot() === CrewTypeEnum::TECHNICAL
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
    public function determineRepairOptions(SpacecraftWrapperInterface $wrapper): array
    {
        $repairOptions = [];

        $ship = $wrapper->get();

        //check for hull option
        $hullPercentage = (int) ($ship->getCondition()->getHull() * 100 / $ship->getMaxHull());
        if ($hullPercentage < RepairTaskConstants::BOTH_MAX) {
            $hullSystem = $this->shipSystemRepository->prototype();
            $hullSystem->setSystemType(SpacecraftSystemTypeEnum::HULL);
            $hullSystem->setStatus($hullPercentage);

            $repairOptions[SpacecraftSystemTypeEnum::HULL->value] = $hullSystem;
        }

        //check for system options
        foreach ($wrapper->getDamagedSystems() as $system) {
            if ($system->getStatus() < RepairTaskConstants::BOTH_MAX) {
                $repairOptions[$system->getSystemType()->value] = $system;
            }
        }

        return $repairOptions;
    }

    #[Override]
    public function createRepairTask(Spacecraft $ship, SpacecraftSystemTypeEnum $systemType, int $repairType, int $finishTime): void
    {
        $obj = $this->repairTaskRepository->prototype();

        $obj->setUser($ship->getUser());
        $obj->setSpacecraft($ship);
        $obj->setSystemType($systemType);
        $obj->setHealingPercentage($this->determineHealingPercentage($repairType));
        $obj->setFinishTime($finishTime);

        $this->repairTaskRepository->save($obj);
    }

    #[Override]
    public function determineHealingPercentage(int $repairType): int
    {
        $percentage = 0;

        if ($repairType === RepairTaskConstants::SPARE_PARTS_ONLY) {
            $percentage += random_int(RepairTaskConstants::SPARE_PARTS_ONLY_MIN, RepairTaskConstants::SPARE_PARTS_ONLY_MAX);
        } elseif ($repairType === RepairTaskConstants::SYSTEM_COMPONENTS_ONLY) {
            $percentage += random_int(RepairTaskConstants::SYSTEM_COMPONENTS_ONLY_MIN, RepairTaskConstants::SYSTEM_COMPONENTS_ONLY_MAX);
        } elseif ($repairType === RepairTaskConstants::BOTH) {
            $percentage += random_int(RepairTaskConstants::BOTH_MIN, RepairTaskConstants::BOTH_MAX);
        }

        return $percentage;
    }

    #[Override]
    public function instantSelfRepair(Spacecraft $spacecraft, SpacecraftSystemTypeEnum $systemType, int $healingPercentage): bool
    {
        return $this->internalSelfRepair(
            $spacecraft,
            $systemType,
            $healingPercentage
        );
    }

    #[Override]
    public function selfRepair(Spacecraft $spacecraft, RepairTask $repairTask): bool
    {
        $systemType = $repairTask->getSystemType();
        $percentage = $repairTask->getHealingPercentage();

        $this->repairTaskRepository->delete($repairTask);

        return $this->internalSelfRepair($spacecraft, $systemType, $percentage);
    }

    private function internalSelfRepair(Spacecraft $spacecraft, SpacecraftSystemTypeEnum $systemType, int $percentage): bool
    {
        $result = true;

        if ($systemType === SpacecraftSystemTypeEnum::HULL) {
            $hullPercentage = (int) ($spacecraft->getCondition()->getHull() * 100 / $spacecraft->getMaxHull());

            if ($hullPercentage > $percentage) {
                $result = false;
            } else {
                $spacecraft->getCondition()->setHull((int)($spacecraft->getMaxHull() * $percentage / 100));
            }
        } else {
            $system = $spacecraft->getSpacecraftSystem($systemType);

            if ($system->getStatus() > $percentage) {
                $result = false;
            } else {
                $system->setStatus($percentage);
                $this->shipSystemRepository->save($system);
            }
        }

        $spacecraft->getCondition()->setState(SpacecraftStateEnum::NONE);

        return $result;
    }

    #[Override]
    public function isRepairStationBonus(SpacecraftWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();

        $colony = $ship->isOverColony();
        if ($colony === null) {
            return false;
        }

        return $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::REPAIR_SHIPYARD);
    }

    #[Override]
    public function getRepairDuration(SpacecraftWrapperInterface $wrapper): int
    {
        $ship = $wrapper->get();
        $ticks = $this->getRepairTicks($wrapper);

        //check if repair station is active
        $colonyRepair = $this->colonyShipRepairRepository->getByShip($ship->getId());
        if ($colonyRepair !== null) {
            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colonyRepair->getColony(), BuildingFunctionEnum::REPAIR_SHIPYARD);
            if ($isRepairStationBonus) {
                $ticks = (int)ceil($ticks / 2);
            }
        }

        return $ticks;
    }

    #[Override]
    public function getRepairDurationPreview(SpacecraftWrapperInterface $wrapper): int
    {
        $ship = $wrapper->get();
        $ticks = $this->getRepairTicks($wrapper);

        $colony = $ship->isOverColony();
        if ($colony !== null) {
            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::REPAIR_SHIPYARD);
            if ($isRepairStationBonus) {
                $ticks = (int)ceil($ticks / 2);
            }
        }

        return $ticks;
    }

    private function getRepairTicks(SpacecraftWrapperInterface $wrapper): int
    {
        $ship = $wrapper->get();
        $ticks = (int) ceil(($ship->getMaxHull() - $ship->getCondition()->getHull()) / self::REPAIR_RATE_PER_TICK);

        return max($ticks, (int) ceil(count($wrapper->getDamagedSystems()) / 2));
    }
}
