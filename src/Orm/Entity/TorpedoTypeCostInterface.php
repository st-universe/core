<?php

namespace Stu\Orm\Entity;

use GoodData;

interface TorpedoTypeCostInterface
{
    public function getId(): int;

    public function getTorpedoType(): TorpedoTypeInterface;

    public function getTorpedoTypeId(): int;

    public function setTorpedoTypeId(int $torpedoTypeId): TorpedoTypeCostInterface;

    public function getGoodId(): int;

    public function setGoodId(int $goodId): TorpedoTypeCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TorpedoTypeCostInterface;

    public function getGood(): GoodData;
}