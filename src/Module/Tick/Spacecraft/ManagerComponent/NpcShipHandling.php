<?php

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class NpcShipHandling implements ManagerComponentInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private StationUtilityInterface $stationUtility,
        private RepairUtilInterface $repairUtil,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private InformationFactoryInterface $informationFactory
    ) {}

    #[Override]
    public function work(): void
    {
        // @todo
        foreach ($this->spacecraftRepository->getNpcSpacecraftsForTick() as $spacecraft) {
            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
            $reactor = $wrapper->getReactorWrapper();
            $this->workSpacecraft($wrapper);
            if ($reactor === null) {
                continue;
            }

            $epsSystem = $wrapper->getEpsSystemData();
            $warpdrive = $wrapper->getWarpDriveSystemData();

            //load EPS
            if ($epsSystem !== null) {
                $epsSystem->setEps($epsSystem->getEps() + $reactor->getEffectiveEpsProduction())->update();
            }

            //load warpdrive
            if ($warpdrive !== null) {
                $warpdrive->setWarpDrive($warpdrive->getWarpDrive() + $reactor->getEffectiveWarpDriveProduction())->update();
            }
        }
    }

    public function workSpacecraft(SpacecraftWrapperInterface $wrapper): void
    {
        $informationWrapper = $this->informationFactory->createInformationWrapper();

        $this->workSpacecraftInternal($wrapper, $informationWrapper);

        $this->sendMessage($wrapper->get(), $informationWrapper);
    }


    private function workSpacecraftInternal(SpacecraftWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        $spacecraft = $wrapper->get();

        // do construction stuff
        if ($spacecraft instanceof StationInterface && $this->doConstructionStuff($spacecraft, $informationWrapper)) {
            $this->spacecraftRepository->save($spacecraft);
            return;
        }

        // repair station
        if ($wrapper instanceof StationWrapperInterface && $spacecraft->getState() === SpacecraftStateEnum::REPAIR_PASSIVE) {
            $this->doRepairStation($wrapper, $informationWrapper);
        }
    }


    private function doConstructionStuff(StationInterface $station, InformationWrapper $informationWrapper): bool
    {
        $progress =  $station->getConstructionProgress();
        if ($progress === null) {
            return false;
        }

        if ($progress->getRemainingTicks() === 0) {
            return false;
        }

        $isUnderConstruction = $station->getState() === SpacecraftStateEnum::UNDER_CONSTRUCTION;

        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $station->getRump())) {
            $neededWorkbees = $isUnderConstruction ? $station->getRump()->getNeededWorkbees() :
                (int)ceil($station->getRump()->getNeededWorkbees() / 2);

            $informationWrapper->addInformationf(
                'Nicht genügend Workbees (%d/%d) angedockt um %s weiterführen zu können',
                $this->stationUtility->getDockedWorkbeeCount($station),
                $neededWorkbees ?? 0,
                $isUnderConstruction ? 'den Bau' : 'die Demontage'
            );
            return true;
        }

        if ($isUnderConstruction) {
            // raise hull
            $increase = (int)ceil($station->getMaxHull() / (2 * $station->getRump()->getBuildtime()));
            $station->setHuell($station->getHull() + $increase);
        }

        if ($progress->getRemainingTicks() === 1) {

            $informationWrapper->addInformationf(
                '%s: %s bei %s fertiggestellt',
                $station->getRump()->getName(),
                $isUnderConstruction ? 'Bau' : 'Demontage',
                $station->getSectorString()
            );

            if ($isUnderConstruction) {
                $this->stationUtility->finishStation($progress);
            } else {
                $this->stationUtility->finishScrapping($progress, $informationWrapper);
            }
        } else {
            $this->stationUtility->reduceRemainingTicks($progress);
        }

        return true;
    }

    private function doRepairStation(StationWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        $station = $wrapper->get();

        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $station->getRump())) {
            $neededWorkbees = (int)ceil($station->getRump()->getNeededWorkbees() / 5);

            $informationWrapper->addInformationf(
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
        $station->setHuell($station->getHull() + $station->getRepairRate());
        if ($station->getHull() > $station->getMaxHull()) {
            $station->setHuell($station->getMaxHull());
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
            $station->setHuell($station->getMaxHull());
            $station->setState(SpacecraftStateEnum::NONE);

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
        $this->spacecraftRepository->save($station);
    }

    private function sendMessage(SpacecraftInterface $ship, InformationWrapper $informationWrapper): void
    {
        if ($informationWrapper->isEmpty()) {
            return;
        }

        $text = sprintf("Tickreport der %s\n%s", $ship->getName(), $informationWrapper->getInformationsAsString());

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $ship->getUser()->getId(),
            $text,
            $ship->getType()->getMessageFolderType(),
            $ship
        );
    }
}
