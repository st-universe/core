<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\Type\EpsShipSystem;
use Stu\Component\Ship\System\Type\HullShipSystem;
use Stu\Component\Ship\System\Type\ProjectileWeaponShipSystem;
use Stu\Component\Ship\System\Type\ShieldShipSystem;
use Stu\Component\Ship\System\Type\TrackerShipSystem;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;

interface ShipWrapperInterface
{
    public function get(): ShipInterface;

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

    public function getHullShipSystem(): HullShipSystem;

    public function getShieldShipSystem(): ?ShieldShipSystem;

    public function getEpsShipSystem(): ?EpsShipSystem;

    public function getProjectileWeaponShipSystem(): ?ProjectileWeaponShipSystem;

    public function getTrackerShipSystem(): ?TrackerShipSystem;
}
