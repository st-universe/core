<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class Matchup
{
    public function __construct(
        private SpacecraftWrapperInterface $attackingWrapper,
        private BattlePartyInterface $targetParty
    ) {}

    public function getAttacker(): SpacecraftWrapperInterface
    {
        return $this->attackingWrapper;
    }

    public function getDefenders(): BattlePartyInterface
    {
        return $this->targetParty;
    }
}
