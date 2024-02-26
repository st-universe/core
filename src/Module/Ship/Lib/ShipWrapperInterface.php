<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShieldSystemData;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\Data\WebEmitterSystemData;
use Stu\Component\Ship\System\Data\WarpDriveSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

interface ShipWrapperInterface
{
    public function get(): ShipInterface;

    public function getShipWrapperFactory(): ShipWrapperFactoryInterface;

    public function getShipSystemManager(): ShipSystemManagerInterface;

    public function getFleetWrapper(): ?FleetWrapperInterface;

    public function getEpsUsage(): int;

    public function lowerEpsUsage(int $value): void;

    public function setAlertState(ShipAlertStateEnum $alertState): ?string;

    /**
     * highest damage first, then prio
     *
     * @return ShipSystemInterface[]
     */
    public function getDamagedSystems(): array;

    public function isOwnedByCurrentUser(): bool;

    public function canLandOnCurrentColony(): bool;

    public function canBeRepaired(): bool;

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

    public function getTractoringShipWrapper(): ?ShipWrapperInterface;

    public function getDockedToShipWrapper(): ?ShipWrapperInterface;

    /**
     * @return array<string>|null
     */
    public function getStateIconAndTitle(): ?array;

    public function getTakeoverTicksLeft(): int;

    public function canBeScrapped(): bool;

    public function getCrewStyle(): string;

    public function getHullSystemData(): HullSystemData;

    public function getShieldSystemData(): ?ShieldSystemData;

    public function getEpsSystemData(): ?EpsSystemData;

    public function getWarpDriveSystemData(): ?WarpDriveSystemData;

    public function getTrackerSystemData(): ?TrackerSystemData;

    public function getWebEmitterSystemData(): ?WebEmitterSystemData;

    public function getReactorWrapper(): ?ReactorWrapperInterface;
}
