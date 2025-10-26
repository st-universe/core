<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use RuntimeException;
use Stu\Component\Spacecraft\System\Data\ComputerSystemData;
use Stu\Component\Spacecraft\System\Data\EnergyWeaponSystemData;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Data\HullSystemData;
use Stu\Component\Spacecraft\System\Data\LssSystemData;
use Stu\Component\Spacecraft\System\Data\ProjectileLauncherSystemData;
use Stu\Component\Spacecraft\System\Data\ShieldSystemData;
use Stu\Component\Spacecraft\System\Data\SubspaceSystemData;
use Stu\Component\Spacecraft\System\Data\WarpDriveSystemData;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftWrapperSystemDataTrait
{
    private function getThis(): SpacecraftWrapper
    {
        if (!$this instanceof SpacecraftWrapper) {
            throw new RuntimeException('trait can only be used on spacecraft wrapper');
        }

        return $this;
    }

    public function getHullSystemData(): HullSystemData
    {
        $hullSystemData = $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::HULL,
            HullSystemData::class
        );

        if ($hullSystemData === null) {
            throw new SystemNotFoundException('no hull installed?');
        }

        return $hullSystemData;
    }

    public function getShieldSystemData(): ?ShieldSystemData
    {
        return $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::SHIELDS,
            ShieldSystemData::class
        );
    }

    public function getEpsSystemData(): ?EpsSystemData
    {
        return $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::EPS,
            EpsSystemData::class
        );
    }

    public function getComputerSystemDataMandatory(): ComputerSystemData
    {
        $computer = $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::COMPUTER,
            ComputerSystemData::class
        );
        if ($computer === null) {
            throw new SystemNotFoundException('no computer installed?');
        }

        return $computer;
    }

    public function getLssSystemData(): ?LssSystemData
    {
        return $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::LSS,
            LssSystemData::class
        );
    }

    public function getEnergyWeaponSystemData(): ?EnergyWeaponSystemData
    {
        return $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::PHASER,
            EnergyWeaponSystemData::class
        );
    }

    public function getWarpDriveSystemData(): ?WarpDriveSystemData
    {
        return $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::WARPDRIVE,
            WarpDriveSystemData::class
        );
    }

    public function getProjectileLauncherSystemData(): ?ProjectileLauncherSystemData
    {
        return $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::TORPEDO,
            ProjectileLauncherSystemData::class
        );
    }

    public function getSubspaceSystemData(): ?SubspaceSystemData
    {
        return $this->getThis()->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::SUBSPACE_SCANNER,
            SubspaceSystemData::class
        );
    }
}
