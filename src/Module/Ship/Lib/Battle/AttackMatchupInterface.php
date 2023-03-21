<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface AttackMatchupInterface
{
    /**
     * @param array<int, ShipWrapperInterface> $attackers
     * @param array<int, ShipWrapperInterface> $defenders
     * @param array<int> $usedShipIds
     *
     */
    public function getMatchup(
        array $attackers,
        array $defenders,
        array &$usedShipIds,
        bool $firstStrike = false,
        bool $oneWay = false,
    ): ?Matchup;
}
