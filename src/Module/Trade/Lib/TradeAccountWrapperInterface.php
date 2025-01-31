<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\StorageInterface;

interface TradeAccountWrapperInterface
{
    public function getId(): int;

    public function getStation(): StationInterface;

    public function getTradePostDescription(): string;

    public function getTradePostName(): string;

    /**
     * @return StorageInterface[]
     */
    public function getStorage(): array;

    public function getStorageSum(): int;

    /**
     * @return array<array{commodity_id: int, amount: int, commodity_name: string}>
     */
    public function getOfferStorage(): array;

    public function getTradeNetwork(): int;

    public function getFreeTransferCapacity(): int;

    public function getTransferCapacity(): int;

    public function isOverStorage(): bool;

    public function getTradePostbyUser(): bool;

    public function getTradePostIsNPC(): bool;

    public function getStorageCapacity(): int;

    public function getLicenseCount(): int;

    public function getFreeStorage(): int;
}