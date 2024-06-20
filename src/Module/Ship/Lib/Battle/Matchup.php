<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class Matchup
{
    public function __construct(
        private ShipWrapperInterface $attackingShipWrapper,
        private BattlePartyInterface $targetParty
    ) {
    }

    public function getAttacker(): ShipWrapperInterface
    {
        return $this->attackingShipWrapper;
    }

    public function getDefenders(): BattlePartyInterface
    {
        return $this->targetParty;
    }
}
