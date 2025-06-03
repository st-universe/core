<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickFinishedException;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

class EnergyConsumeHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly SpacecraftLeaverInterface $spacecraftLeaver
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        $spacecraft = $wrapper->get();
        $hasEnoughCrew = $this->crewAssignmentRepository->hasEnoughCrew($spacecraft);

        $reactorUsageForWarpdrive = $this->loadWarpdrive(
            $wrapper,
            $hasEnoughCrew
        );

        $eps = $wrapper->getEpsSystemData();
        if ($eps === null) {
            return;
        }

        $reactor = $wrapper->getReactorWrapper();

        $availableEps = $this->getAvailableEps(
            $wrapper,
            $eps,
            $reactor,
            $hasEnoughCrew,
            $reactorUsageForWarpdrive
        );

        //try to save energy by reducing alert state
        if ($wrapper->getEpsUsage() > $availableEps) {
            $this->reduceAlertState($wrapper, $availableEps, $information);
        }

        //try to save energy by deactivating systems from low to high priority
        if ($wrapper->getEpsUsage() > $availableEps) {
            $this->deactivateSystems($wrapper, $availableEps, $information);
        }

        $usedEnergy = $this->updateEps($wrapper, $eps, $availableEps, $reactorUsageForWarpdrive);

        if ($usedEnergy > 0 && $reactor !== null) {
            $reactor->changeLoad(-$usedEnergy);
        }
    }

    private function reduceAlertState(
        SpacecraftWrapperInterface $wrapper,
        int $availableEps,
        InformationInterface $information
    ): void {
        $spacecraft = $wrapper->get();
        $malus = $wrapper->getEpsUsage() - $availableEps;
        $alertUsage = $spacecraft->getAlertState()->value - 1;

        if ($alertUsage > 0) {
            $preState = $spacecraft->getAlertState();
            $reduce = min($malus, $alertUsage);

            $spacecraft->setAlertState(SpacecraftAlertStateEnum::from($preState->value - $reduce));
            $information->addInformationf(
                'Wechsel von %s auf %s wegen Energiemangel',
                $preState->getDescription(),
                $spacecraft->getAlertState()->getDescription()
            );
        }
    }

    private function deactivateSystems(SpacecraftWrapperInterface $wrapper, int $availableEps, InformationInterface $information): void
    {
        $activeSystems = $this->spacecraftSystemManager->getActiveSystems($wrapper->get(), true);

        foreach ($activeSystems as $system) {
            $energyConsumption = $this->spacecraftSystemManager->getEnergyConsumption($system->getSystemType());
            if ($energyConsumption < 1) {
                continue;
            }

            if ($availableEps - $wrapper->getEpsUsage() - $energyConsumption < 0) {

                $this->deactivateSystem($wrapper, $system, $energyConsumption, $information);
            }
            if ($wrapper->getEpsUsage() <= $availableEps) {
                break;
            }
        }
    }

    private function deactivateSystem(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemInterface $system,
        int $energyConsumption,
        InformationInterface $information
    ): void {
        $spacecraft = $wrapper->get();
        $this->spacecraftSystemManager->deactivate($wrapper, $system->getSystemType(), true);

        $wrapper->lowerEpsUsage($energyConsumption);
        $information->addInformationf('%s deaktiviert wegen Energiemangel', $system->getSystemType()->getDescription());

        if ($spacecraft->getCrewCount() > 0 && $system->getSystemType() == SpacecraftSystemTypeEnum::LIFE_SUPPORT) {
            $information->addInformation('Die Lebenserhaltung ist ausgefallen:');
            $information->addInformation($this->spacecraftLeaver->evacuate($wrapper));

            throw new SpacecraftTickFinishedException();
        }
    }

    private function calculateBatteryReload(SpacecraftInterface $spacecraft, EpsSystemData $eps, int $newEps): int
    {
        return $spacecraft->isStation()
            && $eps->reloadBattery()
            && $newEps > $eps->getEps()
            ? min(
                (int) ceil($eps->getMaxBattery() / 10),
                $newEps - $eps->getEps(),
                $eps->getMaxBattery() - $eps->getBattery()
            ) : 0;
    }

    private function updateEps(SpacecraftWrapperInterface $wrapper, EpsSystemData $eps, int $availableEps, int $reactorUsageForWarpdrive): int
    {
        $spacecraft = $wrapper->get();

        $newEps = $availableEps - $wrapper->getEpsUsage();
        $batteryReload = $this->calculateBatteryReload($spacecraft, $eps, $newEps);

        $newEps -= $batteryReload;
        if ($newEps > $eps->getMaxEps()) {
            $newEps = $eps->getMaxEps();
        }

        $usedEnergy = $wrapper->getEpsUsage() + $batteryReload + ($newEps - $eps->getEps()) + $reactorUsageForWarpdrive;

        $eps->setEps($newEps)
            ->setBattery($eps->getBattery() + $batteryReload)
            ->update();

        return $usedEnergy;
    }

    private function loadWarpdrive(SpacecraftWrapperInterface $wrapper, bool $hasEnoughCrew): int
    {
        if (!$hasEnoughCrew) {
            return 0;
        }

        $reactor = $wrapper->getReactorWrapper();
        $warpdrive = $wrapper->getWarpDriveSystemData();
        if ($warpdrive === null || $reactor === null) {
            return 0;
        }

        $effectiveWarpdriveProduction = $reactor->getEffectiveWarpDriveProduction();
        if ($effectiveWarpdriveProduction === 0) {
            return 0;
        }

        $currentLoad = $warpdrive->getWarpDrive();

        $warpdrive->setWarpDrive($currentLoad + $effectiveWarpdriveProduction)->update();

        return $effectiveWarpdriveProduction * $wrapper->get()->getRump()->getFlightECost();
    }

    private function getAvailableEps(
        SpacecraftWrapperInterface $wrapper,
        EpsSystemData $eps,
        ?ReactorWrapperInterface $reactor,
        bool $hasEnoughCrew,
        int $reactorUsageForWarpdrive
    ): int {
        if ($hasEnoughCrew && $reactor !== null) {

            return $eps->getEps() + $reactor->getEpsProduction() +  $this->getCarryOver(
                $wrapper,
                $reactor,
                $reactorUsageForWarpdrive
            );
        }

        return $eps->getEps();
    }

    private function getCarryOver(
        SpacecraftWrapperInterface $wrapper,
        ReactorWrapperInterface $reactor,
        int $reactorUsageForWarpdrive
    ): int {
        $warpdrive = $wrapper->getWarpDriveSystemData();
        if ($warpdrive === null || !$warpdrive->getAutoCarryOver()) {
            return 0;
        }

        return $reactor->getOutputCappedByLoad() - $reactor->getEpsProduction() - $reactorUsageForWarpdrive;
    }
}
