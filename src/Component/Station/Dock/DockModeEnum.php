<?php

declare(strict_types=1);

namespace Stu\Component\Station\Dock;

enum DockModeEnum: int
{
    case ALLOW = 1;
    case DENY = 2;

    public function getDescription(): string
    {
        return match ($this) {
            self::ALLOW => 'Erlaubt',
            self::DENY => 'Verboten'
        };
    }
}
