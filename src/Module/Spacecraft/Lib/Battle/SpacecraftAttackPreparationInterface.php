<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;

interface SpacecraftAttackPreparationInterface
{
    public function getReady(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        bool $isOneWay,
        MessageCollectionInterface $messages
    ): void;
}
