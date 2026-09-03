<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Module\Spacecraft\Lib\Battle\Party\RoundBasedBattleParty;

interface AttackMatchupInterface
{
    public function getMatchup(
        RoundBasedBattleParty $attackers,
        RoundBasedBattleParty $defenders,
        bool $firstStrike = false,
        bool $oneWay = false,
    ): ?Matchup;
}
