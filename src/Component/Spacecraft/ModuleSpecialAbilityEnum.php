<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\ModuleSpecial;

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
    case ION_STORM_DAMAGE_REDUCTION = 17;
    case WARPCORE_CHARGE_TRANSFER = 18;

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
            self::ION_STORM_DAMAGE_REDUCTION => 'Ionensturmresistenz',
            self::WARPCORE_CHARGE_TRANSFER => 'Warpkern Ladungstransfer',
        };
    }

    public function getSystemType(): ?SpacecraftSystemTypeEnum
    {
        return match ($this) {
            self::CLOAK => SpacecraftSystemTypeEnum::CLOAK,
            self::RPG => SpacecraftSystemTypeEnum::RPG_MODULE,
            self::TACHYON_SCANNER => SpacecraftSystemTypeEnum::TACHYON_SCANNER,
            self::TROOP_QUARTERS => SpacecraftSystemTypeEnum::TROOP_QUARTERS,
            self::ASTRO_LABORATORY => SpacecraftSystemTypeEnum::ASTRO_LABORATORY,
            self::SUBSPACE_FIELD_SENSOR => SpacecraftSystemTypeEnum::SUBSPACE_SCANNER,
            self::MATRIX_SENSOR => SpacecraftSystemTypeEnum::MATRIX_SCANNER,
            self::TORPEDO_STORAGE => SpacecraftSystemTypeEnum::TORPEDO_STORAGE,
            self::SHUTTLE_RAMP => SpacecraftSystemTypeEnum::SHUTTLE_RAMP,
            self::TRANSWARP_COIL => SpacecraftSystemTypeEnum::TRANSWARP_COIL,
            self::HIROGEN_TRACKER => SpacecraftSystemTypeEnum::TRACKER,
            self::THOLIAN_WEB => SpacecraftSystemTypeEnum::THOLIAN_WEB,
            self::BUSSARD_COLLECTOR => SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR,
            self::AGGREGATION_SYSTEM => SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM,
            self::WARPDRIVE_BOOST => SpacecraftSystemTypeEnum::WARPDRIVE_BOOSTER,
            self::WARPCORE_CHARGE_TRANSFER => SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER,
            default => null
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
            fn (ModuleSpecialAbilityEnum $enum): int => $enum->value,
            ModuleSpecialAbilityEnum::cases()
        );
    }

    /**
     * @param Collection<int, ModuleSpecial> $specials
     */
    public static function getHash(Collection $specials): ?int
    {
        $result = 0;

        foreach ($specials as $special) {
            $result += 2 ** ($special->getSpecialId()->value - 1);
        }

        return $result === 0 ? null : (int)$result;
    }
}
