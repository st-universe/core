<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\SemaphoreEnum;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ProjectileWeaponPhaseInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{
    private ShipRepositoryInterface $shipRepository;

    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    private FightLibInterface $fightLib;

    private LoggerUtilInterface $loggerUtil;

    private SemaphoreUtilInterface $semaphoreUtil;

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

    private bool $oneWay = false;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        EnergyWeaponPhaseInterface $energyWeaponPhase,
        ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        FightLibInterface $fightLib,
        LoggerUtilInterface $loggerUtil,
        SemaphoreUtilInterface $semaphoreUtil
    ) {
        $this->shipRepository = $shipRepository;
        $this->energyWeaponPhase = $energyWeaponPhase;
        $this->projectileWeaponPhase = $projectileWeaponPhase;
        $this->fightLib = $fightLib;
        $this->loggerUtil = $loggerUtil;
        $this->semaphoreUtil = $semaphoreUtil;
    }

    public function init(
        array $attackingShips,
        array $defendingShips,
        bool $oneWay = false
    ): void {
        $this->loggerUtil->init();

        $this->attacker = $attackingShips;
        $this->defender = $defendingShips;
        $this->oneWay = $oneWay;

        //concurrency
        // this already happens in AttackShip, and shouldnt be used here
        // could cause deadlocks in Alert Red situations
        //$this->acquireSemaphores(current($attackingShips)->getUser()->getId());

        $this->firstStrike = true;
        $this->messages = [];
        $this->usedShips = ['attacker' => [], 'defender' => []];
    }

    private function acquireSemaphores(int $userId): void
    {
        $mainSema = $this->semaphoreUtil->getSemaphore(SemaphoreEnum::MAIN_SHIP_SEMAPHORE_KEY, true);
        $this->semaphoreUtil->acquireMainSemaphore($mainSema);

        $this->loggerUtil->log(sprintf('inside main semaphore, userId: %d', $userId));

        $shipSemaphores = [];

        foreach ($this->attacker as $ship) {
            $shipSemaphores[$ship->getId()] = $this->semaphoreUtil->getSemaphore($ship->getId());
            $this->loggerUtil->log(sprintf('  A-shipId: %d', $ship->getId()));
        }
        foreach ($this->defender as $ship) {
            $shipSemaphores[$ship->getId()] = $this->semaphoreUtil->getSemaphore($ship->getId());
            $this->loggerUtil->log(sprintf('  D-shipId: %d', $ship->getId()));
        }

        foreach ($shipSemaphores as $key => $sema) {
            $this->semaphoreUtil->acquireSemaphore($key, $sema);
        }

        $this->loggerUtil->log(sprintf('leaving main semaphore, userId: %d', $userId));
        $this->semaphoreUtil->releaseSemaphore($mainSema);
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
            $this->addMessageMerge($this->fightLib->ready($attacker));
        }
        if (!$this->oneWay) {
            foreach ($this->defender as $defender) {
                $this->addMessageMerge($this->fightLib->ready($defender));
            }
        }

        while (true) {
            $usedAttackerCount = count($this->usedShips['attacker']);
            $usedDefenderCount = count($this->usedShips['defender']);

            // Check if there're any useable ships at all
            if ($usedAttackerCount >= count($this->attacker) && $usedDefenderCount >= count($this->defender)) {
                break;
            }

            $attackerPool = $this->fightLib->filterInactiveShips($this->attacker);
            $defenderPool = $this->fightLib->filterInactiveShips($this->defender);

            if ($attackerPool === [] || $defenderPool === []) {
                break;
            }

            if ($this->firstStrike) {
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
                        return !$this->oneWay && !array_key_exists($ship->getId(), $this->usedShips['defender']) && $this->canFire($ship);
                    }
                );
                if ($readyAttacker === [] && $readyDefender === []) {
                    break;
                }
                if ($readyAttacker === []) {
                    [$attackingShip, $targetShipPool] = $this->getFixture('defender', $readyDefender, $attackerPool);
                } else {
                    $random = rand(1, 2);
                    if ($readyDefender === [] || $random === 1 || $this->oneWay) {
                        [$attackingShip, $targetShipPool] = $this->getFixture('attacker', $readyAttacker, $defenderPool);
                    } else {
                        [$attackingShip, $targetShipPool] = $this->getFixture('defender', $readyDefender, $attackerPool);
                    }
                }
            }

            $this->addMessageMerge($this->energyWeaponPhase->fire($attackingShip, $targetShipPool, $isAlertRed));

            $this->addMessageMerge($this->projectileWeaponPhase->fire($attackingShip, $this->fightLib->filterInactiveShips($targetShipPool), $isAlertRed));
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
}
