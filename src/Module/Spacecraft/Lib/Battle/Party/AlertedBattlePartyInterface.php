<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;

interface AlertedBattlePartyInterface extends BattlePartyInterface
{
    public function getAttackCause(): SpacecraftAttackCauseEnum;

    public function getAlertDescription(): string;
}
