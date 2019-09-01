<?php

namespace Stu\Orm\Entity;

interface TerraformingCostInterface
{
    public function getId(): int;

    public function getTerraformingId(): int;

    public function setTerraformingId(int $terraformingId): TerraformingCostInterface;

    public function getGoodId(): int;

    public function setGoodId(int $goodId): TerraformingCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TerraformingCostInterface;

    public function getGood(): CommodityInterface;

    public function getTerraforming(): TerraformingInterface;
}