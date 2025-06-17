<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\RoundBasedBattleParty;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class Matchup
{
    public function __construct(
        private RoundBasedBattleParty $battleParty,
        private BattlePartyInterface $targetParty
    ) {}

    public function getAttacker(): SpacecraftWrapperInterface
    {
        return $this->battleParty->getRandomUnused();
    }

    public function getDefenders(): BattlePartyInterface
    {
        return $this->targetParty;
    }

    public function isAttackingShieldsOnly(): bool
    {
        return $this->battleParty->get()->isAttackingShieldsOnly();
    }
}
