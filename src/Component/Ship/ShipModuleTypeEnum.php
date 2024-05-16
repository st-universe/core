<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use Crunz\Exception\NotImplementedException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperComputer;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEnergyWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEps;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperHull;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperImpulseDrive;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperInterface;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperProjectileWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperReactor;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperSensor;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperShield;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperSpecial;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperWarpDrive;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;

enum ShipModuleTypeEnum: int
{
    case HULL = 1;
    case SHIELDS = 2;
    case EPS = 3;
    case IMPULSEDRIVE = 4;
    case REACTOR = 5;
    case COMPUTER = 6;
    case PHASER = 7;
    case TORPEDO = 8;
    case SPECIAL = 9;
    case SENSOR = 10;
    case WARPDRIVE = 11;

    public function isSpecialSystemType(): bool
    {
        return $this === self::SPECIAL;
    }

    public function hasCorrespondingSystemType(): bool
    {
        return $this !== self::HULL && !$this->isSpecialSystemType();
    }

    public function getSystemType(): ShipSystemTypeEnum
    {
        if (!$this->hasCorrespondingSystemType()) {
            throw new NotImplementedException('should not be called');
        }

        return match ($this) {
            self::SHIELDS => ShipSystemTypeEnum::SYSTEM_SHIELDS,
            self::WARPDRIVE => ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            self::EPS => ShipSystemTypeEnum::SYSTEM_EPS,
            self::IMPULSEDRIVE => ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE,
            self::REACTOR => ShipSystemTypeEnum::SYSTEM_WARPCORE,
            self::COMPUTER => ShipSystemTypeEnum::SYSTEM_COMPUTER,
            self::PHASER => ShipSystemTypeEnum::SYSTEM_PHASER,
            self::TORPEDO => ShipSystemTypeEnum::SYSTEM_TORPEDO,
            self::SENSOR => ShipSystemTypeEnum::SYSTEM_LSS
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::HULL => "Hülle",
            self::SHIELDS => "Schilde",
            self::EPS => "EPS-Leistung",
            self::IMPULSEDRIVE => "Antrieb",
            self::REACTOR => "Reaktor",
            self::COMPUTER => "Computer",
            self::PHASER => "Energiewaffe",
            self::TORPEDO => "Torpedobank",
            self::SPECIAL => "Spezial",
            self::WARPDRIVE => "Warpantrieb",
            self::SENSOR => "Sensoren"
        };
    }

    public function getModuleRumpWrapperCallable(): callable
    {
        return match ($this) {
            self::HULL => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperHull($rump, $buildplan),
            self::SHIELDS => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperShield($rump, $buildplan),
            self::EPS => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperEps($rump, $buildplan),
            self::IMPULSEDRIVE => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperImpulseDrive($rump, $buildplan),
            self::REACTOR => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperReactor($rump, $buildplan),
            self::COMPUTER => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperComputer($rump, $buildplan),
            self::PHASER => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperEnergyWeapon($rump, $buildplan),
            self::TORPEDO => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperProjectileWeapon($rump, $buildplan),
            self::SENSOR => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperSensor($rump, $buildplan),
            self::WARPDRIVE => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperWarpDrive($rump, $buildplan),
            self::SPECIAL => fn (ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperSpecial($rump, $buildplan)
        };
    }

    /** @return array<ShipModuleTypeEnum> */
    public static function getModuleSelectorOrder(): array
    {
        return [
            self::HULL,
            self::SHIELDS,
            self::COMPUTER,
            self::IMPULSEDRIVE,
            self::SENSOR,
            self::WARPDRIVE,
            self::REACTOR,
            self::EPS,
            self::PHASER,
            self::TORPEDO,
            self::SPECIAL
        ];
    }
}
