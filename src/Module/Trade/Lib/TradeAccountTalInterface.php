<?php

namespace Stu\Module\Trade\Lib;

use Ship;
use Stu\Lib\TradePostStorageWrapper;
use Stu\Orm\Entity\CommodityInterface;
use TradeStorageData;

interface TradeAccountTalInterface
{

    public function getId(): int;

    public function getShip(): Ship;

    public function getTradePostDescription(): string;

    public function getStorage(): TradePostStorageWrapper;

    /**
     * @return TradeStorageData[]
     */
    public function getOfferStorage(): array;

    public function getTradeNetwork(): int;

    public function getFreeTransferCapacity(): int;

    public function getTransferCapacity(): int;

    public function isOverStorage(): bool;

    public function getStorageCapacity(): int;

    public function getLicenseCount(): int;

    public function getFreeStorage(): int;

    public function getTradeLicenseCosts(): int;

    public function getTradeLicenseCostsCommodity(): CommodityInterface;
}