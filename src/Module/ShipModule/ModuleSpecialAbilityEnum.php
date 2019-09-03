<?php

declare(strict_types=1);

namespace Stu\Module\ShipModule;

final class ModuleSpecialAbilityEnum
{
    public const MODULE_SPECIAL_CLOAK = 1;

    public static function getDescription(int $specialId): string {
        switch ($specialId) {
            case static::MODULE_SPECIAL_CLOAK:
                return _('Tarnung');
        }
        return '';
    }
}