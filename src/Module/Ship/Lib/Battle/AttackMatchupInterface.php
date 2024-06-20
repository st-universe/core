<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Party\RoundBasedBattleParty;

interface AttackMatchupInterface
{
    public function getMatchup(
        RoundBasedBattleParty $attackers,
        RoundBasedBattleParty $defenders,
        bool $firstStrike = false,
        bool $oneWay = false,
    ): ?Matchup;
}
