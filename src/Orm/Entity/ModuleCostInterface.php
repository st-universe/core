<?php

namespace Stu\Orm\Entity;

interface ModuleCostInterface
{
    public function getId(): int;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): ModuleCostInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): ModuleCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): ModuleCostInterface;

    public function getCommodity(): CommodityInterface;
}
