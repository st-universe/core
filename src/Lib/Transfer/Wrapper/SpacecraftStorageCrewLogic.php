<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\UplinkShipSystem;
use Stu\Component\Station\Dock\DockPrivilegeUtilityInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;

class SpacecraftStorageCrewLogic
{
    public function __construct(
        private TroopTransferUtilityInterface $troopTransferUtility,
        private DockPrivilegeUtilityInterface $dockPrivilegeUtility,
        private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private SpacecraftShutdownInterface $spacecraftShutdown
    ) {}

    public function getMaxTransferrableCrew(Spacecraft $spacecraft, bool $isTarget, User $user): int
    {
        return min(
            $this->troopTransferUtility->ownCrewOnTarget($user, $spacecraft),
            $isTarget ? PHP_INT_MAX : $this->troopTransferUtility->getBeamableTroopCount($spacecraft)
        );
    }

    public function getFreeCrewSpace(Spacecraft $spacecraft, User $user): int
    {
        if ($user !== $spacecraft->getUser()) {
            if (!$spacecraft->hasUplink()) {
                return 0;
            }

            $userCrewOnTarget = $this->troopTransferUtility->ownCrewOnTarget($user, $spacecraft);
            return $userCrewOnTarget === 0 ? 1 : 0;
        }

        return $this->troopTransferUtility->getFreeQuarters($spacecraft);
    }

    public function checkCrewStorage(SpacecraftWrapperInterface $wrapper, int $amount, bool $isUnload, InformationInterface $information): bool
    {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)) {
            return true;
        }

        $maxRumpCrew = $this->shipCrewCalculator->getMaxCrewCountByRump($spacecraft->getRump());
        $newCrewAmount = $spacecraft->getCrewCount() + ($isUnload ? -$amount : $amount);
        if ($newCrewAmount <= $maxRumpCrew) {
            return true;
        }

        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::TROOP_QUARTERS)) {
            $information->addInformation("Die Truppenquartiere sind zerstört");
            return false;
        }

        if ($spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)->getMode()->isActivated()) {
            return true;
        }

        if (!$this->activatorDeactivatorHelper->activate(
            $wrapper,
            SpacecraftSystemTypeEnum::TROOP_QUARTERS,
            $information
        )) {
            $information->addInformation("Die Truppenquartiere konnten nicht aktiviert werden");
            return false;
        }

        return true;
    }

    public function acceptsCrewFrom(SpacecraftWrapperInterface $wrapper, int $amount, User $user, InformationInterface $information): bool
    {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT)) {
            $information->addInformationf('Die %s hat keine Lebenserhaltungssysteme', $spacecraft->getName());

            return false;
        }

        $needsTroopQuarters = $spacecraft->getCrewCount() + $amount > $this->shipCrewCalculator->getMaxCrewCountByRump($spacecraft->getRump());
        if (
            $needsTroopQuarters
            && $spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            && $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)->getMode() === SpacecraftSystemModeEnum::MODE_OFF
            && !$this->activatorDeactivatorHelper->activate($wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, $information)
        ) {
            return false;
        }

        if ($spacecraft->getUser() === $user) {
            return true;
        }

        if (!$spacecraft instanceof Station) {
            return false;
        }
        if (!$spacecraft->hasUplink()) {
            return false;
        }

        if (!$this->dockPrivilegeUtility->checkPrivilegeFor($spacecraft, $user)) {
            $information->addInformation("Benötigte Andockerlaubnis wurde verweigert");
            return false;
        }
        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::UPLINK)) {
            $information->addInformation("Das Ziel verfügt über keinen intakten Uplink");
            return false;
        }

        if ($this->troopTransferUtility->foreignerCount($spacecraft) >= UplinkShipSystem::MAX_FOREIGNERS) {
            $information->addInformation("Maximale Anzahl an fremden Crewman ist bereits erreicht");
            return false;
        }

        return true;
    }

    public function postCrewTransfer(SpacecraftWrapperInterface $wrapper, int $foreignCrewChangeAmount, InformationInterface $information): void
    {
        $spacecraft = $wrapper->get();

        // no crew left, so shut down
        if ($spacecraft->getCrewCount() === 0) {
            $this->spacecraftShutdown->shutdown($wrapper);
            return;
        }

        if ($foreignCrewChangeAmount !== 0) {

            $ownCrew = $this->getOwnCrewCount($spacecraft);
            $minOwnCrew = 0;
            $buildplan = $spacecraft->getBuildplan();
            if ($buildplan !== null) {
                $minOwnCrew = $buildplan->getCrew();
            }

            $hasForeigners = $this->troopTransferUtility->foreignerCount($spacecraft) > 0;
            if (
                !$hasForeigners
                && $spacecraft->getSystemState(SpacecraftSystemTypeEnum::UPLINK)
            ) {
                $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
            if (
                $hasForeigners
                && !$spacecraft->getSystemState(SpacecraftSystemTypeEnum::UPLINK)
                && $ownCrew >= $minOwnCrew
            ) {
                $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            }

            $this->sendUplinkMessage($hasForeigners, $information, $spacecraft->getSystemState(SpacecraftSystemTypeEnum::UPLINK), $ownCrew >= $minOwnCrew);
        }

        if (
            $spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            && $spacecraft->getSystemState(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            && $spacecraft->getBuildplan() !== null
            && $spacecraft->getCrewCount() <= $this->shipCrewCalculator->getMaxCrewCountByRump($spacecraft->getRump())
        ) {
            $this->activatorDeactivatorHelper->deactivate($wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, $information);
        }

        if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT)) {
            return;
        }

        if (
            $spacecraft->getCrewCount() > 0
            && !$spacecraft->getSystemState(SpacecraftSystemTypeEnum::LIFE_SUPPORT)
        ) {
            $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::LIFE_SUPPORT, true);
        }
    }

    private function sendUplinkMessage(bool $hasForeigners, InformationInterface $information, bool $state, bool $enoughOwnCrew): void
    {
        if (!$hasForeigners) {
            $information->addInformationf(
                'Der Uplink ist %s',
                $state ? 'aktiviert' : 'deaktiviert'
            );
        } else {
            $information->addInformationf(
                'Der Uplink %s%s',
                $state ? 'ist aktiviert' : 'bleibt deaktiviert',
                $enoughOwnCrew ? '' : '. Es befindet sich nicht ausreichend Crew des Besitzers an Bord'
            );
        }
    }

    private function getOwnCrewCount(Spacecraft $spacecraft): int
    {
        $count = 0;
        foreach ($spacecraft->getCrewAssignments() as $spacecraftCrew) {
            if ($spacecraftCrew->getCrew()->getUser() === $spacecraft->getUser()) {
                $count++;
            }
        }
        return $count;
    }
}
