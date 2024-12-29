<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Data\HullSystemData;
use Stu\Component\Spacecraft\System\Data\ProjectileLauncherSystemData;
use Stu\Component\Spacecraft\System\Data\ShieldSystemData;
use Stu\Component\Spacecraft\System\Data\WarpDriveSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\ShipRepairCost;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

interface SpacecraftWrapperInterface
{
    public function get(): SpacecraftInterface;

    public function getFleetWrapper(): ?FleetWrapperInterface;

    public function getSpacecraftWrapperFactory(): SpacecraftWrapperFactoryInterface;

    public function getSpacecraftSystemManager(): SpacecraftSystemManagerInterface;

    public function getEpsUsage(): int;

    public function lowerEpsUsage(int $value): void;

    public function setAlertState(SpacecraftAlertStateEnum $alertState): ?string;

    /**
     * highest damage first, then prio
     *
     * @return SpacecraftSystemInterface[]
     */
    public function getDamagedSystems(): array;

    public function isSelectable(): bool;

    public function canBeRepaired(): bool;

    public function canFire(): bool;

    public function getRepairDuration(): int;

    public function getRepairDurationPreview(): int;

    /**
     * @return array{0: ShipRepairCost, 1: ShipRepairCost}
     */
    public function getRepairCosts(): array;

    /**
     * @return array<int, TorpedoTypeInterface>
     */
    public function getPossibleTorpedoTypes(): array;

    public function getTractoredShipWrapper(): ?ShipWrapperInterface;

    /**
     * @return array<string>|null
     */
    public function getStateIconAndTitle(): ?array;

    public function getTakeoverTicksLeft(?ShipTakeoverInterface $takeover = null): int;

    public function getCrewStyle(): string;

    public function getHullSystemData(): HullSystemData;

    public function getShieldSystemData(): ?ShieldSystemData;

    public function getEpsSystemData(): ?EpsSystemData;

    public function getWarpDriveSystemData(): ?WarpDriveSystemData;

    public function getProjectileLauncherSystemData(): ?ProjectileLauncherSystemData;

    public function getReactorWrapper(): ?ReactorWrapperInterface;
}
