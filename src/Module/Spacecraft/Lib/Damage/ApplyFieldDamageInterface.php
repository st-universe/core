<?php

namespace Stu\Module\Spacecraft\Lib\Damage;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ApplyFieldDamageInterface
{
    public function damage(
        SpacecraftWrapperInterface $wrapper,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        MessageCollectionInterface $messages
    ): void;
}
