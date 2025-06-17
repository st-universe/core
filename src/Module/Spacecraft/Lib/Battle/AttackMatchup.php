<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Doctrine\Common\Collections\ArrayCollection;
use Override;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Battle\Party\RoundBasedBattleParty;

final class AttackMatchup implements AttackMatchupInterface
{
    public function __construct(
        private StuRandom $stuRandom
    ) {}

    #[Override]
    public function getMatchup(
        RoundBasedBattleParty $attackers,
        RoundBasedBattleParty $defenders,
        bool $firstStrike = false,
        bool $oneWay = false
    ): ?Matchup {

        // Check if there're any useable ships at all
        if ($attackers->isDone() && $defenders->isDone()) {
            return null;
        }

        if ($firstStrike) {
            return $this->getMatchupInternal($attackers, $defenders);
        } else {
            return $this->getMatchupForFurtherStrike(
                $attackers,
                $defenders,
                $oneWay
            );
        }
    }

    private function getMatchupInternal(
        RoundBasedBattleParty $attackers,
        RoundBasedBattleParty $defenders
    ): ?Matchup {

        if ($attackers->isDone()) {
            return null;
        }

        return new Matchup(
            $attackers->getRandomUnused(),
            $defenders->get()
        );
    }

    private function getMatchupForFurtherStrike(
        RoundBasedBattleParty $attackers,
        RoundBasedBattleParty $defenders,
        bool $oneWay
    ): ?Matchup {
        $attackersDone = $attackers->isDone();
        $defendersDone = $oneWay ? new ArrayCollection() : $defenders->isDone();

        if ($attackersDone && $defendersDone) {
            return null;
        }

        if ($attackersDone) {
            return $this->getMatchupInternal($defenders, $attackers);
        } elseif ($defendersDone || $this->stuRandom->rand(1, 2) === 1) {
            return $this->getMatchupInternal($attackers, $defenders);
        } else {
            return $this->getMatchupInternal($defenders, $attackers);
        }
    }
}
