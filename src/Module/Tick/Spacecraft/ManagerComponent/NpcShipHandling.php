<?php

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\Handler\StationConstructionHandler;
use Stu\Module\Tick\Spacecraft\Handler\StationPassiveRepairHandler;
use Stu\Module\Tick\Spacecraft\SpacecraftTickFinishedException;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class NpcShipHandling implements ManagerComponentInterface
{
    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly StationConstructionHandler $stationConstructionHandler,
        private readonly StationPassiveRepairHandler $stationPassiveRepairHandler,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly InformationFactoryInterface $informationFactory
    ) {}

    #[\Override]
    public function work(): void
    {
        foreach ($this->spacecraftRepository->getNpcSpacecraftsForTick() as $spacecraft) {

            $informationWrapper = $this->informationFactory->createInformationWrapper();
            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

            $this->workSpacecraft($wrapper, $informationWrapper);

            $this->sendMessage($wrapper->get(), $informationWrapper);
        }
    }

    private function workSpacecraft(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {

        try {
            $this->stationConstructionHandler->handleSpacecraftTick($wrapper, $information);
            $this->stationPassiveRepairHandler->handleSpacecraftTick($wrapper, $information);
        } catch (SpacecraftTickFinishedException) {
            return;
        }

        $reactor = $wrapper->getReactorWrapper();
        if ($reactor === null) {
            return;
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

    private function sendMessage(Spacecraft $ship, InformationWrapper $informationWrapper): void
    {
        if ($informationWrapper->isEmpty()) {
            return;
        }

        $text = sprintf("Tickreport der %s\n%s", $ship->getName(), $informationWrapper->getInformationsAsString());

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $ship->getUser()->getId(),
            $text,
            $ship->getType()->getMessageFolderType(),
            $ship
        );
    }
}
