<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface TradePostInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): TradePostInterface;

    public function getName(): string;

    public function setName(string $name): TradePostInterface;

    public function getDescription(): string;

    public function setDescription(string $description): TradePostInterface;

    public function getShipId(): int;

    public function setShipId(int $shipId): TradePostInterface;

    public function getTradeNetwork(): int;

    public function setTradeNetwork(int $tradeNetwork): TradePostInterface;

    public function getLevel(): int;

    public function setLevel(int $level): TradePostInterface;

    public function getTransferCapacity(): int;

    public function setTransferCapacity(int $transferCapacity): TradePostInterface;

    public function getStorage(): int;

    public function setStorage(int $storage): TradePostInterface;

    public function isDockPmAutoRead(): bool;

    public function setIsDockPmAutoRead(bool $value): TradePostInterface;

    public function getLatestLicenseInfo(): ?TradeLicenseInfoInterface;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): TradePostInterface;

    /**
     * @return Collection<int, ShipCrewInterface>
     */
    public function getCrewAssignments(): Collection;

    public function getCrewCountOfUser(
        UserInterface $user
    ): int;

    public function isNpcTradepost(): bool;
}
