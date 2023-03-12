<?php

namespace Stu\Orm\Entity;

interface TerraformingCostInterface
{
    public function getId(): int;

    public function getTerraformingId(): int;

    public function setTerraformingId(int $terraformingId): TerraformingCostInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): TerraformingCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TerraformingCostInterface;

    public function getCommodity(): CommodityInterface;

    public function getTerraforming(): TerraformingInterface;
}
