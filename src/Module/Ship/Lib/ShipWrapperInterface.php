<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\Type\EpsShipSystem;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;

interface ShipWrapperInterface
{
    public function get(): ShipInterface;

    public function getEpsUsage(): int;

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

    public function getEpsShipSystem(): ?EpsShipSystem;
}
