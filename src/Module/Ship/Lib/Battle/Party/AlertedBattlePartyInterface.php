<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;

interface AlertedBattlePartyInterface extends BattlePartyInterface
{
    public function getAttackCause(): ShipAttackCauseEnum;

    public function getAlertDescription(): string;
}
