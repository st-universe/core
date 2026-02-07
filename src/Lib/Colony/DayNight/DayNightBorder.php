<?php

declare(strict_types=1);

namespace Stu\Lib\Colony\DayNight;

use Stu\Orm\Entity\PlanetField;

class DayNightBorder
{
    public static function getDayNightPrefix(PlanetField $field, int $timestamp): string
    {
        $twilightZone = $field->getHost()->getTwilightZone($timestamp);

        if ($twilightZone >= 0) {
            return $field->getFieldId() % $field->getHost()->getSurfaceWidth() >= $twilightZone ? 'n' : 't';
        }

        return $field->getFieldId() % $field->getHost()->getSurfaceWidth() < -$twilightZone ? 'n' : 't';
    }
}
