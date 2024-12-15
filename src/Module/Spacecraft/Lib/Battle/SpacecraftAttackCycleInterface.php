<?php

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;

interface SpacecraftAttackCycleInterface
{
    public function cycle(
        BattlePartyInterface $attackingShips,
        BattlePartyInterface $defendingShips,
        SpacecraftAttackCauseEnum $attackCause
    ): MessageCollectionInterface;
}
