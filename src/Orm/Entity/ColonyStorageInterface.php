<?php

namespace Stu\Orm\Entity;

interface ColonyStorageInterface
{
    public function getId(): int;

    public function getColonyId(): int;

    public function setColonyId(int $colonyId): ColonyStorageInterface;

    public function getGoodId(): int;

    public function setGoodId(int $commodityId): ColonyStorageInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): ColonyStorageInterface;

    public function getGood(): CommodityInterface;

    public function setGood(CommodityInterface $commodity): ColonyStorageInterface;
}