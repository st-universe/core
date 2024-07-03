<?php

declare(strict_types=1);

namespace Stu\Module\ShipModule;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ModuleSpecialInterface;

final class ModuleSpecialAbilityEnum
{
    public const int MODULE_SPECIAL_CLOAK = 1;
    public const int MODULE_SPECIAL_RPG = 2;
    public const int MODULE_SPECIAL_TACHYON_SCANNER = 4;
    public const int MODULE_SPECIAL_TROOP_QUARTERS = 5;
    public const int MODULE_SPECIAL_ASTRO_LABORATORY = 6;
    public const int MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR = 7;
    public const int MODULE_SPECIAL_MATRIX_SENSOR = 8;
    public const int MODULE_SPECIAL_TORPEDO_STORAGE = 9;
    public const int MODULE_SPECIAL_SHUTTLE_RAMP = 10;
    public const int MODULE_SPECIAL_TRANSWARP_COIL = 11;
    public const int MODULE_SPECIAL_HIROGEN_TRACKER = 12;
    public const int MODULE_SPECIAL_THOLIAN_WEB = 13;

    public static function getDescription(int $specialId): string
    {
        return match ($specialId) {
            static::MODULE_SPECIAL_CLOAK => _('Tarnung'),
            static::MODULE_SPECIAL_RPG => _('RPG-Schiff'),
            static::MODULE_SPECIAL_TACHYON_SCANNER => _('Tachyon-Scanner'),
            static::MODULE_SPECIAL_TROOP_QUARTERS => _('Truppen-Quartiere'),
            static::MODULE_SPECIAL_ASTRO_LABORATORY => _('Astrometrie-Labor'),
            static::MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR => _('Subraumfeldsensor'),
            static::MODULE_SPECIAL_MATRIX_SENSOR => _('Matrix-Sensor'),
            static::MODULE_SPECIAL_TORPEDO_STORAGE => _('Torpedo-Lager'),
            static::MODULE_SPECIAL_SHUTTLE_RAMP => _('Shuttle-Rampe'),
            static::MODULE_SPECIAL_TRANSWARP_COIL => _('Transwarpspule'),
            static::MODULE_SPECIAL_HIROGEN_TRACKER => _('Tracker-Device'),
            static::MODULE_SPECIAL_THOLIAN_WEB => _('Tholianischer Netzemitter'),
            default => '',
        };
    }

    /**
     * @param Collection<int, ModuleSpecialInterface> $specials
     */
    public static function getHash($specials): ?int
    {
        $result = 0;

        foreach ($specials as $special) {
            $result += 2 ** ($special->getSpecialId() - 1);
        }

        return $result == 0 ? null : $result;
    }
}
