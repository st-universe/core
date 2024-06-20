<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;

interface ShipAttackPreparationInterface
{

    public function getReady(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        bool $isOneWay,
        MessageCollectionInterface $messages
    ): void;
}
