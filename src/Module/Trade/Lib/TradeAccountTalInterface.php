<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradeStorageInterface;

interface TradeAccountTalInterface
{

    public function getId(): int;

    public function getShip(): ShipInterface;

    public function getTradePostDescription(): string;

    /**
     * @return TradeStorageInterface[]
     */
    public function getStorage(): array;

    public function getStorageSum(): int;

    /**
     * @return TradeStorageInterface[]
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

    public function getTradeLicenseCosts(): int;

    public function getTradeLicenseCostsCommodity(): CommodityInterface;
}