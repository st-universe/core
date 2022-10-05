<?php

namespace Stu\Orm\Entity;

interface StorageInterface
{
    public function getId(): int;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): StorageInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): StorageInterface;

    public function getCommodity(): CommodityInterface;

    public function setCommodity(CommodityInterface $commodity): StorageInterface;

    public function getShip(): ?ShipInterface;

    public function setShip(ShipInterface $ship): StorageInterface;
}
