<?php

declare(strict_types=1);

namespace Stu\Module\ShipModule;

final class ModuleSpecialAbilityEnum
{
    public const MODULE_SPECIAL_CLOAK = 1;
    public const MODULE_SPECIAL_RPG = 2;
    public const MODULE_SPECIAL_TOMATO_PEELER = 42;

    public static function getDescription(int $specialId): string {
        switch ($specialId) {
            case static::MODULE_SPECIAL_CLOAK:
                return _('Tarnung');
            case static::MODULE_SPECIAL_RPG:
                return _('RPG-Schiff');
            case static::MODULE_SPECIAL_TOMATO_PEELER:
                return _('Tomaten-Schäler');
        }
        return '';
    }
}
