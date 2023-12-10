<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Lock;

enum LockTypeEnum: int
{
    case COLONY_GROUP = 1;
    case SHIP_GROUP = 2;

    public function getName(): string
    {
        return match ($this) {
            self::COLONY_GROUP => 'colonyGroup',
            self::SHIP_GROUP => 'shipGroup'
        };
    }
}
