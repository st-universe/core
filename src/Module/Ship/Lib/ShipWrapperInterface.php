<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShieldSystemData;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\Data\WebEmitterSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;

interface ShipWrapperInterface
{
    public function get(): ShipInterface;

    public function getShipWrapperFactory(): ShipWrapperFactoryInterface;

    public function getShipSystemManager(): ShipSystemManagerInterface;

    public function getFleetWrapper(): ?FleetWrapperInterface;

    public function getEpsUsage(): int;

    public function lowerEpsUsage($value): void;

    public function getEffectiveEpsProduction(): int;

    public function getWarpcoreUsage(): int;

    public function setAlertState(int $alertState, &$msg): void;

    public function leaveFleet(): void;

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

    public function getRepairCosts(): array;

    public function getPossibleTorpedoTypes(): array;

    public function getTractoredShipWrapper(): ?ShipWrapperInterface;

    public function getTractoringShipWrapper(): ?ShipWrapperInterface;

    public function getHullSystemData(): HullSystemData;

    public function getShieldSystemData(): ?ShieldSystemData;

    public function getEpsSystemData(): ?EpsSystemData;

    public function getTrackerSystemData(): ?TrackerSystemData;

    public function getWebEmitterSystemData(): ?WebEmitterSystemData;
}
