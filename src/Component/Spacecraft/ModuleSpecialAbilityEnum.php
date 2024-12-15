<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\ModuleSpecialInterface;

enum ModuleSpecialAbilityEnum: int
{
    case MODULE_SPECIAL_CLOAK = 1;
    case MODULE_SPECIAL_RPG = 2;
    case MODULE_SPECIAL_TACHYON_SCANNER = 4;
    case MODULE_SPECIAL_TROOP_QUARTERS = 5;
    case MODULE_SPECIAL_ASTRO_LABORATORY = 6;
    case MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR = 7;
    case MODULE_SPECIAL_MATRIX_SENSOR = 8;
    case MODULE_SPECIAL_TORPEDO_STORAGE = 9;
    case MODULE_SPECIAL_SHUTTLE_RAMP = 10;
    case MODULE_SPECIAL_TRANSWARP_COIL = 11;
    case MODULE_SPECIAL_HIROGEN_TRACKER = 12;
    case MODULE_SPECIAL_THOLIAN_WEB = 13;
    case MODULE_SPECIAL_BUSSARD_COLLECTOR = 14;
    case MODULE_SPECIAL_AGGREGATION_SYSTEM = 15;

    public function getDescription(): string
    {
        return match ($this) {
            self::MODULE_SPECIAL_CLOAK => _('Tarnung'),
            self::MODULE_SPECIAL_RPG => _('RPG-Schiff'),
            self::MODULE_SPECIAL_TACHYON_SCANNER => _('Tachyon-Scanner'),
            self::MODULE_SPECIAL_TROOP_QUARTERS => _('Truppen-Quartiere'),
            self::MODULE_SPECIAL_ASTRO_LABORATORY => _('Astrometrie-Labor'),
            self::MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR => _('Subraumfeldsensor'),
            self::MODULE_SPECIAL_MATRIX_SENSOR => _('Matrix-Sensor'),
            self::MODULE_SPECIAL_TORPEDO_STORAGE => _('Torpedo-Lager'),
            self::MODULE_SPECIAL_SHUTTLE_RAMP => _('Shuttle-Rampe'),
            self::MODULE_SPECIAL_TRANSWARP_COIL => _('Transwarpspule'),
            self::MODULE_SPECIAL_HIROGEN_TRACKER => _('Tracker-Device'),
            self::MODULE_SPECIAL_THOLIAN_WEB => _('Tholianischer Netzemitter'),
            self::MODULE_SPECIAL_BUSSARD_COLLECTOR => _('Bussard-Kollektor'),
            self::MODULE_SPECIAL_AGGREGATION_SYSTEM => _('Aggregationssystem'),
        };
    }

    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return match ($this) {
            self::MODULE_SPECIAL_CLOAK => SpacecraftSystemTypeEnum::SYSTEM_CLOAK,
            self::MODULE_SPECIAL_RPG => SpacecraftSystemTypeEnum::SYSTEM_RPG_MODULE,
            self::MODULE_SPECIAL_TACHYON_SCANNER => SpacecraftSystemTypeEnum::SYSTEM_TACHYON_SCANNER,
            self::MODULE_SPECIAL_TROOP_QUARTERS => SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS,
            self::MODULE_SPECIAL_ASTRO_LABORATORY => SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY,
            self::MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR => SpacecraftSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER,
            self::MODULE_SPECIAL_MATRIX_SENSOR => SpacecraftSystemTypeEnum::SYSTEM_MATRIX_SCANNER,
            self::MODULE_SPECIAL_TORPEDO_STORAGE => SpacecraftSystemTypeEnum::SYSTEM_TORPEDO_STORAGE,
            self::MODULE_SPECIAL_SHUTTLE_RAMP => SpacecraftSystemTypeEnum::SYSTEM_SHUTTLE_RAMP,
            self::MODULE_SPECIAL_TRANSWARP_COIL => SpacecraftSystemTypeEnum::SYSTEM_TRANSWARP_COIL,
            self::MODULE_SPECIAL_HIROGEN_TRACKER => SpacecraftSystemTypeEnum::SYSTEM_TRACKER,
            self::MODULE_SPECIAL_THOLIAN_WEB => SpacecraftSystemTypeEnum::SYSTEM_THOLIAN_WEB,
            self::MODULE_SPECIAL_BUSSARD_COLLECTOR => SpacecraftSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR,
            self::MODULE_SPECIAL_AGGREGATION_SYSTEM => SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM
        };
    }

    public function hasCorrespondingModule(): bool
    {
        return match ($this) {
            self::MODULE_SPECIAL_RPG => false,
            default => true
        };
    }

    /** @return array<int> */
    public static function getValueArray(): array
    {
        return array_map(
            fn(ModuleSpecialAbilityEnum $enum): int => $enum->value,
            ModuleSpecialAbilityEnum::cases()
        );
    }

    /**
     * @param Collection<int, ModuleSpecialInterface> $specials
     */
    public static function getHash($specials): ?int
    {
        $result = 0;

        foreach ($specials as $special) {
            $result += 2 ** ($special->getSpecialId()->value - 1);
        }

        return $result == 0 ? null : (int)$result;
    }
}
