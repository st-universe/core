<?php

namespace Stu\Module\Ship\Lib\Damage;

use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ApplyFieldDamageInterface
{
    public function damage(
        ShipWrapperInterface $wrapper,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        FightMessageCollectionInterface $messages
    ): void;
}
