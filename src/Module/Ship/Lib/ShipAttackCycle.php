<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\FightMessage;
use Stu\Module\Ship\Lib\Battle\FightMessageCollection;
use Stu\Module\Ship\Lib\Battle\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\FightMessageInterface;
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

    private FightMessageCollectionInterface $messages;

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
        $this->messages = new FightMessageCollection();
        $this->usedShips = ['attacker' => [], 'defender' => []];
    }

    /**
     * @param string $usedShipKey
     * @param ShipWrapperInterface[] $attacker
     * @param ShipWrapperInterface[] $defender
     */
    private function getFixture(string $usedShipKey, array $attacker, array $defender): array
    {
        if ($attacker === []) {
            die();
        }

        $attackingShip = $attacker[array_rand($attacker)];
        $this->usedShips[$usedShipKey][$attackingShip->get()->getId()] = $attackingShip;

        return [
            $attackingShip,
            $defender,
        ];
    }

    public function cycle(bool $isAlertRed = false): void
    {
        foreach ($this->attacker as $attacker) {
            $this->addMessageMerge($attacker->get()->getUser()->getId(), $this->fightLib->ready($attacker));
        }
        if (!$this->oneWay) {
            foreach ($this->defender as $defender) {
                $this->addMessageMerge($defender->get()->getUser()->getId(), $this->fightLib->ready($defender));
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

            $this->addFightMessages($this->energyWeaponPhase->fire(
                $attackingShipWrapper,
                null,
                $targetShipWrappers,
                $isAlertRed
            ));

            $this->addFightMessages($this->projectileWeaponPhase->fire(
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

    /**
     * @param FightMessageInterface[] $messages
     */
    private function addFightMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->messages->add($message);
        }
    }

    /**
     * @param string[] $msg
     */
    private function addMessageMerge(int $senderId, $msg): void
    {
        $fightMessage = new FightMessage($senderId, null);
        $fightMessage->addMessageMerge($msg);
        $this->messages->add($fightMessage);
    }

    public function getMessages(): FightMessageCollectionInterface
    {
        return $this->messages;
    }

    private function canFire(ShipWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();
        if ($ship->getDisabled()) {
            return false;
        }

        $epsSystem = $wrapper->getEpsSystemData();
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
