<?php

namespace Stu\Module\Message\Lib;

enum ContactListModeEnum: int
{
    case FRIEND = 1;
    case ENEMY = 2;
    case NEUTRAL = 3;

    public function getTitle(): string
    {
        return match ($this) {
            self::FRIEND => 'Freund',
            self::ENEMY => 'Feind',
            self::NEUTRAL => 'Neutral'
        };
    }
}
