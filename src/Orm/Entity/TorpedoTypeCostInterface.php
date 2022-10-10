<?php

namespace Stu\Orm\Entity;

interface TorpedoTypeCostInterface
{
    public function getId(): int;

    public function getTorpedoType(): TorpedoTypeInterface;

    public function getTorpedoTypeId(): int;

    public function setTorpedoTypeId(int $torpedoTypeId): TorpedoTypeCostInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): TorpedoTypeCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TorpedoTypeCostInterface;

    public function getCommodity(): CommodityInterface;
}
