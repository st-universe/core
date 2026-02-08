<?php

declare(strict_types=1);

namespace Stu\Component\Player;

enum UserCssClassEnum: string
{
    case BLACK = 'schwarz';
    case PURPLE = 'lila';
    case GREEN = 'grün';
    case YELLOW = 'gelb';
    case RED = 'rot';
    case LCARS = 'lcars';
    case ORANGE = 'orange';

    public function getTitle(): string
    {
        return match ($this) {
            self::BLACK => 'Schwarz',
            self::PURPLE => 'Lila',
            self::GREEN => 'Grün',
            self::YELLOW => 'Gelb',
            self::RED => 'Rot',
            self::LCARS => 'LCARS',
            self::ORANGE => 'Orange',
        };
    }
}
