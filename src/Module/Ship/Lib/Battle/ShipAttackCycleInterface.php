<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;

interface ShipAttackCycleInterface
{
    public function cycle(
        BattlePartyInterface $attackingShips,
        BattlePartyInterface $defendingShips,
        ShipAttackCauseEnum $attackCause
    ): MessageCollectionInterface;
}
