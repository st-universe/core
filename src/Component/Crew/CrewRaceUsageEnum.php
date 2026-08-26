<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

enum CrewRaceUsageEnum: string
{
    case STANDARD = 'standard';
    case STANDARD_AND_FOREIGN = 'standard_and_foreign';
    case FOREIGN_ONLY = 'foreign_only';

    public function getTitle(): string
    {
        return match ($this) {
            self::STANDARD => 'Nur Standard-Crew',
            self::STANDARD_AND_FOREIGN => 'Standard- und freigeschaltete Crew',
            self::FOREIGN_ONLY => 'Nur freigeschaltete Crew'
        };
    }
}
