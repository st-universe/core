<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

enum CrewGenderEnum: int
{
    case MALE = 1;
    case FEMALE = 2;

    public function getShort(): string
    {
        return match ($this) {
            self::MALE => "m",
            self::FEMALE => "w"
        };
    }
}
