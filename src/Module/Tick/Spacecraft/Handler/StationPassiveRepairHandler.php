<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Spacecraft\Repair\RepairUtil;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;

class StationPassiveRepairHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private StationUtilityInterface $stationUtility,
        private RepairUtilInterface $repairUtil,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        $spacecraft = $wrapper->get();

        if (
            $wrapper instanceof StationWrapperInterface
            && $spacecraft->getState() === SpacecraftStateEnum::REPAIR_PASSIVE
        ) {
            $this->doRepairStation($wrapper, $information);
        }
    }

    private function doRepairStation(StationWrapperInterface $wrapper, InformationInterface $information): void
    {
        $station = $wrapper->get();

        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $station->getRump())) {
            $neededWorkbees = (int)ceil($station->getRump()->getNeededWorkbees() / 5);

            $information->addInformationf(
                'Nicht genügend Workbees (%d/%d) angedockt um die Reparatur weiterführen zu können',
                $this->stationUtility->getDockedWorkbeeCount($station),
                $neededWorkbees
            );
            return;
        }

        $neededParts = $this->repairUtil->determineSpareParts($wrapper, true);

        // parts stored?
        if (!$this->repairUtil->enoughSparePartsOnEntity($neededParts, $station, $station)) {
            return;
        }

        //repair hull
        $condition = $station->getCondition();
        $condition->changeHull(RepairUtil::REPAIR_RATE_PER_TICK);
        if ($condition->getHull() > $station->getMaxHull()) {
            $condition->setHull($station->getMaxHull());
        }

        //repair station systems
        $damagedSystems = $wrapper->getDamagedSystems();
        if ($damagedSystems !== []) {
            $firstSystem = $damagedSystems[0];
            $firstSystem->setStatus(100);

            if ($station->getCrewCount() > 0) {
                $firstSystem->setMode($this->spacecraftSystemManager->lookupSystem($firstSystem->getSystemType())->getDefaultMode());
            }

            // maximum of two systems get repaired
            if (count($damagedSystems) > 1) {
                $secondSystem = $damagedSystems[1];
                $secondSystem->setStatus(100);

                if ($station->getCrewCount() > 0) {
                    $secondSystem->setMode($this->spacecraftSystemManager->lookupSystem($secondSystem->getSystemType())->getDefaultMode());
                }
            }
        }

        // consume spare parts
        $this->repairUtil->consumeSpareParts($neededParts, $station);

        if (!$wrapper->canBeRepaired()) {
            $condition->setHull($station->getMaxHull());
            $condition->setState(SpacecraftStateEnum::NONE);

            $shipOwnerMessage = sprintf(
                "Die Reparatur der %s wurde in Sektor %s fertiggestellt",
                $station->getName(),
                $station->getSectorString()
            );

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $station->getUser()->getId(),
                $shipOwnerMessage,
                PrivateMessageFolderTypeEnum::SPECIAL_STATION
            );
        }
    }
}
