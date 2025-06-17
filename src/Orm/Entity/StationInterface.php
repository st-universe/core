<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface StationInterface extends SpacecraftInterface
{
    public function getTradePost(): ?TradePostInterface;

    public function setTradePost(?TradePostInterface $tradePost): StationInterface;

    public function getConstructionProgress(): ?ConstructionProgressInterface;

    public function resetConstructionProgress(): StationInterface;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function setInfluenceArea(?StarSystemInterface $influenceArea): StationInterface;

    /**
     * @return Collection<int, DockingPrivilegeInterface>
     */
    public function getDockPrivileges(): Collection;

    public function hasFreeDockingSlots(): bool;

    public function getDockingSlotCount(): int;

    public function getFreeDockingSlotCount(): int;

    public function getDockedShipCount(): int;

    /**
     * @return Collection<int, ShipInterface>
     */
    public function getDockedShips(): Collection;

    public function getDockedWorkbeeCount(): int;

    public function getConstructionHubState(): bool;

    public function isAggregationSystemHealthy(): bool;
}
