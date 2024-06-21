<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

enum PlayerRelationTypeEnum
{
    case NONE;
    case USER;
    case ALLY;

    public function isDominant(): bool
    {
        return match ($this) {
            self::NONE => false,
            self::USER => true,
            self::ALLY => false
        };
    }
}
