<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class AttackMatchup implements AttackMatchupInterface
{
    private FightLibInterface $fightLib;

    private StuRandom $stuRandom;

    public function __construct(
        FightLibInterface $fightLib,
        StuRandom $stuRandom
    ) {
        $this->fightLib = $fightLib;
        $this->stuRandom = $stuRandom;
    }

    public function getMatchup(
        array $attackers,
        array $defenders,
        array &$usedShipIds,
        bool $firstStrike = false,
        bool $oneWay = false
    ): ?Matchup {
        // Check if there're any useable ships at all
        if ($this->isEveryShipUsed($attackers, $defenders, $usedShipIds)) {
            return null;
        }

        $attackerPool = $this->fightLib->filterInactiveShips($attackers);
        $defenderPool = $this->fightLib->filterInactiveShips($defenders);

        if (empty($attackerPool) || empty($defenderPool)) {
            return null;
        }

        if ($firstStrike) {
            return $this->getMatchupInternal($attackerPool, $defenderPool, $usedShipIds);
        } else {
            return $this->getMatchupForFurtherStrike(
                $attackerPool,
                $defenderPool,
                $oneWay,
                $usedShipIds
            );
        }
    }

    /**
     * @param array<ShipWrapperInterface> $attackers
     * @param array<ShipWrapperInterface> $defenders
     * @param array<int> $usedShipIds
     */
    private function isEveryShipUsed(
        array $attackers,
        array $defenders,
        array $usedShipIds
    ): bool {
        foreach ($attackers as $wrapper) {
            if (!in_array($wrapper->get()->getId(), $usedShipIds)) {
                return false;
            }
        }
        foreach ($defenders as $wrapper) {
            if (!in_array($wrapper->get()->getId(), $usedShipIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, ShipWrapperInterface> $attackers
     * @param array<int, ShipWrapperInterface> $defenders
     * @param array<int> $usedShipIds
     *
     */
    private function getMatchupInternal(array $attackers, array $defenders, array &$usedShipIds): ?Matchup
    {
        if (empty($attackers)) {
            return null;
        }

        $attackingShip = $attackers[$this->stuRandom->array_rand($attackers)];
        $usedShipIds[] = $attackingShip->get()->getId();

        return new Matchup(
            $attackingShip,
            $defenders
        );
    }

    /**
     * @param array<ShipWrapperInterface> $attackers
     * @param array<ShipWrapperInterface> $defenders
     * @param array<int> $usedShipIds
     *
     */
    private function getMatchupForFurtherStrike(
        array $attackers,
        array $defenders,
        bool $oneWay,
        array &$usedShipIds
    ): ?Matchup {
        $readyAttackers = array_filter(
            $attackers,
            fn(ShipWrapperInterface $wrapper): bool => !in_array($wrapper->get()->getId(), $usedShipIds)
                && $this->fightLib->canFire($wrapper)
        );
        $readyDefenders = array_filter(
            $defenders,
            fn(ShipWrapperInterface $wrapper): bool => !$oneWay && !in_array($wrapper->get()->getId(), $usedShipIds)
                && $this->fightLib->canFire($wrapper)
        );
        if (empty($readyAttackers) && empty($readyDefenders)) {
            return null;
        }
        if (empty($readyAttackers)) {
            return $this->getMatchupInternal($readyDefenders, $attackers, $usedShipIds);
        } else {
            if (empty($readyDefenders) || $this->stuRandom->rand(1, 2) === 1) {
                return $this->getMatchupInternal($readyAttackers, $defenders, $usedShipIds);
            } else {
                return $this->getMatchupInternal($readyDefenders, $attackers, $usedShipIds);
            }
        }
    }
}
