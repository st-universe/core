<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use Crunz\Exception\NotImplementedException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
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
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

enum SpacecraftModuleTypeEnum: int
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

    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return match ($this) {
            self::SHIELDS => SpacecraftSystemTypeEnum::SHIELDS,
            self::WARPDRIVE => SpacecraftSystemTypeEnum::WARPDRIVE,
            self::EPS => SpacecraftSystemTypeEnum::EPS,
            self::IMPULSEDRIVE => SpacecraftSystemTypeEnum::IMPULSEDRIVE,
            self::REACTOR => SpacecraftSystemTypeEnum::WARPCORE,
            self::COMPUTER => SpacecraftSystemTypeEnum::COMPUTER,
            self::PHASER => SpacecraftSystemTypeEnum::PHASER,
            self::TORPEDO => SpacecraftSystemTypeEnum::TORPEDO,
            self::SENSOR => SpacecraftSystemTypeEnum::LSS,
            default => throw new NotImplementedException('should not be called')
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
            self::HULL => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperHull($rump, $buildplan),
            self::SHIELDS => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperShield($rump, $buildplan),
            self::EPS => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperEps($rump, $buildplan),
            self::IMPULSEDRIVE => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperImpulseDrive($rump, $buildplan),
            self::REACTOR => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperReactor($rump, $buildplan),
            self::COMPUTER => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperComputer($rump, $buildplan),
            self::PHASER => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperEnergyWeapon($rump, $buildplan),
            self::TORPEDO => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperProjectileWeapon($rump, $buildplan),
            self::SENSOR => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperSensor($rump, $buildplan),
            self::WARPDRIVE => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperWarpDrive($rump, $buildplan),
            self::SPECIAL => fn(SpacecraftRumpInterface $rump, ?SpacecraftBuildplanInterface $buildplan): ModuleRumpWrapperInterface => new ModuleRumpWrapperSpecial($rump, $buildplan)
        };
    }

    public function getOrder(): int
    {
        return match ($this) {
            self::HULL => 1,
            self::SHIELDS => 2,
            self::COMPUTER => 3,
            self::IMPULSEDRIVE => 4,
            self::SENSOR => 5,
            self::WARPDRIVE => 6,
            self::REACTOR => 7,
            self::EPS => 8,
            self::PHASER => 9,
            self::TORPEDO => 10,
            self::SPECIAL => 99
        };
    }

    /** @return array<SpacecraftModuleTypeEnum> */
    public static function getModuleSelectorOrder(): array
    {
        $cases = self::cases();

        usort(
            $cases,
            fn(SpacecraftModuleTypeEnum $a, SpacecraftModuleTypeEnum $b): int => $a->getOrder() <=> $b->getOrder()
        );

        return $cases;
    }
}
