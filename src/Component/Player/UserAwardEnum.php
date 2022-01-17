<?php

declare(strict_types=1);

namespace Stu\Component\Player;

final class UserAwardEnum
{
    // award types
    public const RESEARCHED_STATIONS = 1;

    public static function getDescription(int $awardType): string
    {
        switch ($awardType) {
            case UserAwardEnum::RESEARCHED_STATIONS:
                return _("Stationen erforscht");
        }
        return '';
    }
}
