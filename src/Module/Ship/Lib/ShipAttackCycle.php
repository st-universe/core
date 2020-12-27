<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\ProjectileWeaponPhaseInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{
    private EntryCreatorInterface $entryCreator;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private WeaponRepositoryInterface $weaponRepository;

    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    /**
     * @return ShipInterface[]
     */
    private array $attacker = [];

    /**
     * @return ShipInterface[]
     */
    private array $defender = [];

    private bool $firstStrike = true;

    private array $messages = [];

    private array $usedShips = ['attacker' => [], 'defender' => []];

    private bool $singleMode = false;

    public function __construct(
        EntryCreatorInterface $entryCreator,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        WeaponRepositoryInterface $weaponRepository,
        EnergyWeaponPhaseInterface $energyWeaponPhase,
        ProjectileWeaponPhaseInterface $projectileWeaponPhase
    ) {
        $this->entryCreator = $entryCreator;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->weaponRepository = $weaponRepository;
        $this->energyWeaponPhase = $energyWeaponPhase;
        $this->projectileWeaponPhase = $projectileWeaponPhase;
    }

    public function init(
        array $attackingShips,
        array $defendingShips,
        bool $singleMode = false
    ): void {
        $this->attacker = $attackingShips;
        $this->defender = $defendingShips;
        $this->singleMode = $singleMode;
        $this->firstStrike = true;
        $this->messages = [];
        $this->usedShips = ['attacker' => [], 'defender' => []];
    }

    /**
     * @return ShipInterface[]
     */
    private function filterInactiveShips(array $base): array
    {
        return array_filter(
            $base,
            function (ShipInterface $ship): bool {
                return !$ship->getIsDestroyed() && !$ship->getDisabled();
            }
        );
    }

    private function ready(ShipInterface $ship): array
    {
        try {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_CLOAK);
        } catch (ShipSystemException $e) {
        }

        $ship->cancelRepair();

        $msg = $this->alertLevelBasedReaction($ship);

        if ($msg !== []) {
            $msg = array_merge([sprintf(_('Aktionen der %s'), $ship->getName())], $msg);
        }

        return $msg;
    }

    /**
     * @param string $usedShipKey
     * @param ShipInterface[] $attacker
     * @param ShipInterface[] $defender
     */
    private function getFixture(string $usedShipKey, array $attacker, array $defender): array
    {
        if ($attacker === []) die();

        $attackingShip = $attacker[array_rand($attacker)];
        $this->usedShips[$usedShipKey][$attackingShip->getId()] = $attackingShip;

        return [
            $attackingShip,
            $defender
        ];
    }

    public function cycle(bool $isAlertRed = false): void
    {
        foreach ($this->attacker as $attacker) {
            $this->addMessageMerge($this->ready($attacker));
        }
        foreach ($this->defender as $defender) {
            $this->addMessageMerge($this->ready($defender));
        }

        while (true) {
            $usedAttackerCount = count($this->usedShips['attacker']);
            $usedDefenderCount = count($this->usedShips['defender']);

            // Check if there're any useable ships at all
            if ($usedAttackerCount >= count($this->attacker) && $usedDefenderCount >= count($this->defender)) {
                break;
            }

            $attackerPool = $this->filterInactiveShips($this->attacker);
            $defenderPool = $this->filterInactiveShips($this->defender);

            if ($attackerPool === [] || $defenderPool === []) {
                break;
            }

            if ($this->firstStrike || $this->singleMode) {
                $this->firstStrike = false;

                [$attackingShip, $targetShipPool] = $this->getFixture('attacker', $attackerPool, $defenderPool);
            } else {
                $readyAttacker = array_filter(
                    $attackerPool,
                    function (ShipInterface $ship): bool {
                        return !array_key_exists($ship->getId(), $this->usedShips['attacker']) && $this->canFire($ship);
                    }
                );
                $readyDefender = array_filter(
                    $defenderPool,
                    function (ShipInterface $ship): bool {
                        return !array_key_exists($ship->getId(), $this->usedShips['defender']) && $this->canFire($ship);
                    }
                );
                if ($readyAttacker === [] && $readyDefender === []) {
                    break;
                }
                if ($readyAttacker === []) {
                    [$attackingShip, $targetShipPool] = $this->getFixture('defender', $readyDefender, $attackerPool);
                } else {
                    $random = rand(1, 2);
                    if ($readyDefender === [] || $random === 1) {
                        [$attackingShip, $targetShipPool] = $this->getFixture('attacker', $readyAttacker, $defenderPool);
                    } else {
                        [$attackingShip, $targetShipPool] = $this->getFixture('defender', $readyDefender, $attackerPool);
                    }
                }
            }

            $this->addMessageMerge($this->energyWeaponPhase->fire($attackingShip, $targetShipPool, $isAlertRed));

            $this->addMessageMerge($this->projectileWeaponPhase->fire($attackingShip, $this->filterInactiveShips($targetShipPool), $isAlertRed));
        }

        foreach ($this->attacker as $ship) {
            $this->shipRepository->save($ship);
        }

        foreach ($this->defender as $ship) {
            $this->shipRepository->save($ship);
        }

    }

    private function addMessageMerge($msg): void
    {
        $this->messages = array_merge($this->getMessages(), $msg);
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    private function canFire(ShipInterface $ship): bool
    {
        if ($ship->getDisabled()) {
            return false;
        }
        if ($ship->getEps() === 0) {
            return false;
        }
        if (!$ship->getNbs()) {
            return false;
        }
        if (!$ship->canAttack()) {
            return false;
        }
        return true;
    }

    private function alertLevelBasedReaction(ShipInterface $ship): array
    {
        $msg = [];
        if ($ship->getCrewCount() < $ship->getBuildplan()->getCrew() || $ship->getRump()->isTrumfield()) {
            return $msg;
        }
        if ($ship->getDockedTo()) {
            $ship->setDockedTo(null);
            $msg[] = "- Das Schiff hat abgedockt";
        }
        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_GREEN) {
            try {
                $ship->setAlertState(ShipAlertStateEnum::ALERT_YELLOW);
                $msg[] = "- Erhöhung der Alarmstufe wurde durchgeführt, Grün -> Gelb";
                return $msg;
            } catch (ShipSystemException $e) {
                $msg[] = "- Nicht genügend Energie vorhanden um auf Alarm-Gelb zu wechseln";
                return $msg;
            }
        }
        if ($ship->getCloakState() && $ship->getAlertState() == ShipAlertStateEnum::ALERT_YELLOW) {
            try {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_CLOAK);
                $msg[] = "- Die Tarnung wurde deaktiviert";
                return $msg;
            } catch (ShipSystemException $e) {
            }
        }
        if (!$ship->isTraktorbeamActive()) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);

                $msg[] = "- Die Schilde wurden aktiviert";
            } catch (ShipSystemException $e) {
            }
        } else {
            $msg[] = "- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden";
        }
        try {
            $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_NBS);

            $msg[] = "- Die Nahbereichssensoren wurden aktiviert";
        } catch (ShipSystemException $e) {
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_YELLOW) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_PHASER);

                $msg[] = "- Die Energiewaffe wurde aktiviert";
            } catch (ShipSystemException $e) {
            }
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_RED) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_TORPEDO);

                $msg[] = "- Der Torpedowerfer wurde aktiviert";
            } catch (ShipSystemException $e) {
            }
        }
        return $msg;
    }
}
