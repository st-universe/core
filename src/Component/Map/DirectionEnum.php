<?php

declare(strict_types=1);

namespace Stu\Component\Map;

enum DirectionEnum: int
{
    case NON = 0;
    case LEFT = 1;
    case BOTTOM = 2;
    case RIGHT = 3;
    case TOP = 4;

    public function getOpposite(): DirectionEnum
    {
        return match ($this) {
            self::LEFT => self::RIGHT,
            self::BOTTOM => self::TOP,
            self::RIGHT => self::LEFT,
            self::TOP => self::BOTTOM,
            self::NON => self::NON
        };
    }
}
