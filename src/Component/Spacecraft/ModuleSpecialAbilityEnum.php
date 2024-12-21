<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\ModuleSpecialInterface;

enum ModuleSpecialAbilityEnum: int
{
    case CLOAK = 1;
    case RPG = 2;
    case TACHYON_SCANNER = 4;
    case TROOP_QUARTERS = 5;
    case ASTRO_LABORATORY = 6;
    case SUBSPACE_FIELD_SENSOR = 7;
    case MATRIX_SENSOR = 8;
    case TORPEDO_STORAGE = 9;
    case SHUTTLE_RAMP = 10;
    case TRANSWARP_COIL = 11;
    case HIROGEN_TRACKER = 12;
    case THOLIAN_WEB = 13;
    case BUSSARD_COLLECTOR = 14;
    case AGGREGATION_SYSTEM = 15;
    case WARPDRIVE_BOOST = 16;

    public function getDescription(): string
    {
        return match ($this) {
            self::CLOAK => 'Tarnung',
            self::RPG => 'RPG-Schiff',
            self::TACHYON_SCANNER => 'Tachyon-Scanner',
            self::TROOP_QUARTERS => 'Truppen-Quartiere',
            self::ASTRO_LABORATORY => 'Astrometrie-Labor',
            self::SUBSPACE_FIELD_SENSOR => 'Subraumfeldsensor',
            self::MATRIX_SENSOR => 'Matrix-Sensor',
            self::TORPEDO_STORAGE => 'Torpedo-Lager',
            self::SHUTTLE_RAMP => 'Shuttle-Rampe',
            self::TRANSWARP_COIL => 'Transwarpspule',
            self::HIROGEN_TRACKER => 'Tracker-Device',
            self::THOLIAN_WEB => 'Tholianischer Netzemitter',
            self::BUSSARD_COLLECTOR => 'Bussard-Kollektor',
            self::AGGREGATION_SYSTEM => 'Aggregationssystem',
            self::WARPDRIVE_BOOST => 'Warpdrive Boost',
        };
    }

    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return match ($this) {
            self::CLOAK => SpacecraftSystemTypeEnum::SYSTEM_CLOAK,
            self::RPG => SpacecraftSystemTypeEnum::SYSTEM_RPG_MODULE,
            self::TACHYON_SCANNER => SpacecraftSystemTypeEnum::SYSTEM_TACHYON_SCANNER,
            self::TROOP_QUARTERS => SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS,
            self::ASTRO_LABORATORY => SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY,
            self::SUBSPACE_FIELD_SENSOR => SpacecraftSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER,
            self::MATRIX_SENSOR => SpacecraftSystemTypeEnum::SYSTEM_MATRIX_SCANNER,
            self::TORPEDO_STORAGE => SpacecraftSystemTypeEnum::SYSTEM_TORPEDO_STORAGE,
            self::SHUTTLE_RAMP => SpacecraftSystemTypeEnum::SYSTEM_SHUTTLE_RAMP,
            self::TRANSWARP_COIL => SpacecraftSystemTypeEnum::SYSTEM_TRANSWARP_COIL,
            self::HIROGEN_TRACKER => SpacecraftSystemTypeEnum::SYSTEM_TRACKER,
            self::THOLIAN_WEB => SpacecraftSystemTypeEnum::SYSTEM_THOLIAN_WEB,
            self::BUSSARD_COLLECTOR => SpacecraftSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR,
            self::AGGREGATION_SYSTEM => SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM,
            self::WARPDRIVE_BOOST => SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE_BOOSTER
        };
    }

    public function hasCorrespondingModule(): bool
    {
        return match ($this) {
            self::RPG => false,
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
