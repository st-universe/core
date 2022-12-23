<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ProjectileWeaponPhaseInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{
    private ShipRepositoryInterface $shipRepository;

    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    private FightLibInterface $fightLib;

    private LoggerUtilInterface $loggerUtil;

    /**
     * @var ShipWrapperInterface[]
     */
    private array $attacker = [];

    /**
     * @var ShipWrapperInterface[]
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
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->energyWeaponPhase = $energyWeaponPhase;
        $this->projectileWeaponPhase = $projectileWeaponPhase;
        $this->fightLib = $fightLib;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function init(
        array $attackingShips,
        array $defendingShips,
        bool $oneWay = false
    ): void {
        $this->attacker = $attackingShips;
        $this->defender = $defendingShips;
        $this->oneWay = $oneWay;

        $this->firstStrike = true;
        $this->messages = [];
        $this->usedShips = ['attacker' => [], 'defender' => []];
    }

    /**
     * @param string $usedShipKey
     * @param ShipWrapperInterface[] $attacker
     * @param ShipWrapperInterface[] $defender
     */
    private function getFixture(string $usedShipKey, array $attacker, array $defender): array
    {
        if ($attacker === []) die();

        $attackingShip = $attacker[array_rand($attacker)];
        $this->usedShips[$usedShipKey][$attackingShip->get()->getId()] = $attackingShip;

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
                //$this->loggerUtil->init('SAC', LoggerEnum::LEVEL_ERROR);
                //$this->loggerUtil->log(sprintf('attackerCount: %d, defenderCount: %d', count($this->attacker), count($this->defender)));
                break;
            }

            $attackerPool = $this->fightLib->filterInactiveShips($this->attacker);
            $defenderPool = $this->fightLib->filterInactiveShips($this->defender);

            if (empty($attackerPool) || empty($defenderPool)) {
                //$this->loggerUtil->init('SAC', LoggerEnum::LEVEL_ERROR);
                //$this->loggerUtil->log(sprintf('attackerPoolCount: %d, defenderPoolCount: %d', count($attackerPool), count($defenderPool)));
                break;
            }

            if ($this->firstStrike) {
                $this->firstStrike = false;

                [$attackingShipWrapper, $targetShipWrappers] = $this->getFixture('attacker', $attackerPool, $defenderPool);
            } else {
                $readyAttacker = array_filter(
                    $attackerPool,
                    function (ShipWrapperInterface $wrapper): bool {
                        return !array_key_exists($wrapper->get()->getId(), $this->usedShips['attacker']) && $this->canFire($wrapper);
                    }
                );
                $readyDefender = array_filter(
                    $defenderPool,
                    function (ShipWrapperInterface $wrapper): bool {
                        return !$this->oneWay && !array_key_exists($wrapper->get()->getId(), $this->usedShips['defender']) && $this->canFire($wrapper);
                    }
                );
                if ($readyAttacker === [] && $readyDefender === []) {
                    break;
                }
                if ($readyAttacker === []) {
                    [$attackingShipWrapper, $targetShipWrappers] = $this->getFixture('defender', $readyDefender, $attackerPool);
                } else {
                    $random = rand(1, 2);
                    if ($readyDefender === [] || $random === 1 || $this->oneWay) {
                        [$attackingShipWrapper, $targetShipWrappers] = $this->getFixture('attacker', $readyAttacker, $defenderPool);
                    } else {
                        [$attackingShipWrapper, $targetShipWrappers] = $this->getFixture('defender', $readyDefender, $attackerPool);
                    }
                }
            }

            $this->addMessageMerge($this->energyWeaponPhase->fire(
                $attackingShipWrapper,
                null,
                $targetShipWrappers,
                $isAlertRed
            ));

            $this->addMessageMerge($this->projectileWeaponPhase->fire(
                $attackingShipWrapper,
                null,
                $this->fightLib->filterInactiveShips($targetShipWrappers),
                $isAlertRed
            ));
        }

        foreach ($this->attacker as $wrapper) {
            $this->shipRepository->save($wrapper->get());
        }

        foreach ($this->defender as $wrapper) {
            $this->shipRepository->save($wrapper->get());
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

    private function canFire(ShipWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();
        if ($ship->getDisabled()) {
            return false;
        }

        $epsSystem = $wrapper->getEpsShipSystem();
        if ($epsSystem->getEps() === 0) {
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
