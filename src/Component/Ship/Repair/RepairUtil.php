<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Repair;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Crew\CrewEnum;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class RepairUtil implements RepairUtilInterface
{
    //TODO Unit-Tests!
    private ShipSystemRepositoryInterface $shipSystemRepository;

    private RepairTaskRepositoryInterface $repairTaskRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        RepairTaskRepositoryInterface $repairTaskRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ColonyStorageManagerInterface $colonyStorageManager,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->repairTaskRepository = $repairTaskRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->privateMessageSender = $privateMessageSender;
    }

    //REPAIR STUFF
    public function determineSpareParts(ShipInterface $ship): array
    {
        $neededSpareParts = 0;
        $neededSystemComponents = 0;

        $hull = $ship->getHull();
        $maxHull = $ship->getMaxHull();

        if ($hull < $maxHull) {
            $neededSpareParts += (int)($ship->getRepairRate() / RepairTaskEnum::HULL_HITPOINTS_PER_SPARE_PART);
        }

        $damagedSystems = $this->shipWrapperFactory->wrapShip($ship)->getDamagedSystems();
        if (!empty($damagedSystems)) {
            $firstSystem = $damagedSystems[0];
            $firstSystemLvl = $firstSystem->determineSystemLevel();
            $healingPercentage = (100 - $firstSystem->getStatus()) / 100;

            $neededSpareParts += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$firstSystemLvl][RepairTaskEnum::SPARE_PARTS_ONLY]);
            $neededSystemComponents += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$firstSystemLvl][RepairTaskEnum::SYSTEM_COMPONENTS_ONLY]);

            // maximum of two systems get repaired
            if (count($damagedSystems) > 1) {
                $secondSystem = $damagedSystems[1];
                $secondSystemLvl = $secondSystem->determineSystemLevel();
                $healingPercentage = (100 - $secondSystem->getStatus()) / 100;

                $neededSpareParts += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$secondSystemLvl][RepairTaskEnum::SPARE_PARTS_ONLY]);
                $neededSystemComponents += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$secondSystemLvl][RepairTaskEnum::SYSTEM_COMPONENTS_ONLY]);
            }
        }

        return [
            CommodityTypeEnum::COMMODITY_SPARE_PART => $neededSpareParts,
            CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT => $neededSystemComponents
        ];
    }

    public function enoughSparePartsOnEntity(array $neededParts, $entity, bool $isColony, ShipInterface $ship): bool
    {
        $neededSpareParts = $neededParts[CommodityTypeEnum::COMMODITY_SPARE_PART];
        $neededSystemComponents = $neededParts[CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT];

        if ($neededSpareParts > 0) {
            $spareParts = $entity->getStorage()->get(CommodityTypeEnum::COMMODITY_SPARE_PART);

            if ($spareParts === null || $spareParts->getAmount() < $neededSpareParts) {
                $this->sendNeededAmountMessage($neededSpareParts, $neededSystemComponents, $ship, $entity, $isColony);
                return false;
            }
        }

        if ($neededSystemComponents > 0) {
            $systemComponents = $entity->getStorage()->get(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT);

            if ($systemComponents === null || $systemComponents->getAmount() < $neededSystemComponents) {
                $this->sendNeededAmountMessage($neededSpareParts, $neededSystemComponents, $ship, $entity, $isColony);
                return false;
            }
        }

        return true;
    }

    private function sendNeededAmountMessage(int $neededSpareParts, int $neededSystemComponents, ShipInterface $ship, $entity, bool $isColony): void
    {
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
            $entity->getUserId(),
            $entityOwnerMessage,
            $isColony ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY : PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
        );
    }

    public function consumeSpareParts(array $neededParts, $entity, bool $isColony): void
    {
        foreach ($neededParts as $commodityKey => $amount) {
            //$this->loggerUtil->log(sprintf('consume, cid: %d, amount: %d', $commodityKey, $amount));

            if ($amount < 1) {
                continue;
            }

            $commodity = $entity->getStorage()->get($commodityKey)->getCommodity();

            if ($isColony) {
                $this->colonyStorageManager->lowerStorage($entity, $commodity, $amount);
            } else {
                $this->shipStorageManager->lowerStorage($entity, $commodity, $amount);
            }
        }
    }


    //SELFREPAIR STUFF

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

    public function determineRepairOptions(ShipInterface $ship): array
    {
        $repairOptions = [];

        //check for hull option
        $hullPercentage = (int) ($ship->getHull() * 100 / $ship->getMaxHull());
        if ($hullPercentage < RepairTaskEnum::BOTH_MAX) {
            $hullSystem = $this->shipSystemRepository->prototype();
            $hullSystem->setSystemType(ShipSystemTypeEnum::SYSTEM_HULL);
            $hullSystem->setStatus($hullPercentage);

            $repairOptions[ShipSystemTypeEnum::SYSTEM_HULL] = $hullSystem;
        }

        //check for system options
        foreach ($this->shipWrapperFactory->wrapShip($ship)->getDamagedSystems() as $system) {
            if ($system->getStatus() < RepairTaskEnum::BOTH_MAX) {
                $repairOptions[$system->getSystemType()] = $system;
            }
        }

        return $repairOptions;
    }

    public function createRepairTask(ShipInterface $ship, int $systemType, int $repairType, int $finishTime): void
    {
        $obj = $this->repairTaskRepository->prototype();

        $obj->setUser($ship->getUser());
        $obj->setShip($ship);
        $obj->setSystemType($systemType);
        $obj->setHealingPercentage($this->determineHealingPercentage($repairType));
        $obj->setFinishTime($finishTime);

        $this->repairTaskRepository->save($obj);
    }

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

    public function instantSelfRepair($ship, $systemType, $healingPercentage): bool
    {
        return $this->internalSelfRepair(
            $ship,
            $systemType,
            $healingPercentage
        );
    }

    public function selfRepair(ShipInterface $ship, RepairTaskInterface $repairTask): bool
    {
        $systemType = $repairTask->getSystemType();
        $percentage = $repairTask->getHealingPercentage();

        $this->repairTaskRepository->delete($repairTask);

        return $this->internalSelfRepair($ship, $systemType, $percentage);
    }

    private function internalSelfRepair(ShipInterface $ship, int $systemType, int $percentage): bool
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
}
