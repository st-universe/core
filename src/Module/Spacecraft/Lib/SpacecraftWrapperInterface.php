<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Data\ComputerSystemData;
use Stu\Component\Spacecraft\System\Data\EnergyWeaponSystemData;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Data\HullSystemData;
use Stu\Component\Spacecraft\System\Data\LssSystemData;
use Stu\Component\Spacecraft\System\Data\ProjectileLauncherSystemData;
use Stu\Component\Spacecraft\System\Data\ShieldSystemData;
use Stu\Component\Spacecraft\System\Data\SubspaceSystemData;
use Stu\Component\Spacecraft\System\Data\WarpDriveSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\TorpedoType;

interface SpacecraftWrapperInterface
{
    public function get(): Spacecraft;

    public function getFleetWrapper(): ?FleetWrapperInterface;

    public function getSpacecraftWrapperFactory(): SpacecraftWrapperFactoryInterface;

    public function getSpacecraftSystemManager(): SpacecraftSystemManagerInterface;

    public function getEpsUsage(): int;

    public function lowerEpsUsage(int $value): void;

    public function getAlertState(): SpacecraftAlertStateEnum;

    public function setAlertState(SpacecraftAlertStateEnum $alertState): ?string;

    public function isUnalerted(): bool;

    public function getShieldRegenerationRate(): int;

    /**
     * highest damage first, then prio
     *
     * @return SpacecraftSystem[]
     */
    public function getDamagedSystems(): array;

    public function isSelectable(): bool;

    public function canBeRepaired(): bool;

    public function canFire(): bool;

    public function canMan(): bool;

    public function getRepairDuration(): int;

    public function getRepairDurationPreview(): int;

    /**
     * @return array{0: ShipRepairCost, 1: ShipRepairCost}
     */
    public function getRepairCosts(): array;

    /**
     * @return array<int, TorpedoType>
     */
    public function getPossibleTorpedoTypes(): array;

    public function getTractoredShipWrapper(): ?ShipWrapperInterface;

    /**
     * @return array<string>|null
     */
    public function getStateIconAndTitle(): ?array;

    public function getTakeoverTicksLeft(?ShipTakeover $takeover = null): int;

    public function getCrewStyle(): string;

    public function getHullSystemData(): HullSystemData;

    public function getShieldSystemData(): ?ShieldSystemData;

    public function getEpsSystemData(): ?EpsSystemData;

    /** The consumer of this method has to be sure that spacecraft.hasComputer returns true */
    public function getComputerSystemDataMandatory(): ComputerSystemData;

    public function getLssSystemData(): ?LssSystemData;

    public function getEnergyWeaponSystemData(): ?EnergyWeaponSystemData;

    public function getWarpDriveSystemData(): ?WarpDriveSystemData;
    public function getProjectileLauncherSystemData(): ?ProjectileLauncherSystemData;

    public function getSubspaceSystemData(): ?SubspaceSystemData;

    public function getReactorWrapper(): ?ReactorWrapperInterface;

    public function __toString(): string;
}
