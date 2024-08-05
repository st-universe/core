<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface LocationMiningInterface
{
    public function getId(): int;

    public function getLocationId(): int;

    public function setLocationId(int $location_id): void;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodity_id): void;

    public function getActualAmount(): int;

    public function setActualAmount(int $actual_amount): void;

    public function getMaxAmount(): int;

    public function setMaxAmount(int $max_amount): void;

    public function getDepletedAt(): ?int;

    public function setDepletedAt(?int $depleted_at): void;

    public function getLocation(): LocationInterface;

    public function setLocation(LocationInterface $location): void;

    public function getCommodity(): CommodityInterface;

    public function setCommodity(CommodityInterface $commodity): void;
}
