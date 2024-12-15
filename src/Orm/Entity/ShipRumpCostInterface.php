<?php

namespace Stu\Orm\Entity;

interface ShipRumpCostInterface
{
    public function getId(): int;

    public function getRumpId(): int;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): ShipRumpCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): ShipRumpCostInterface;

    public function getCommodity(): CommodityInterface;
}
