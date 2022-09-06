<?php

namespace Stu\Orm\Entity;

interface ResearchInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): ResearchInterface;

    public function getDescription(): string;

    public function setDescription(string $description): ResearchInterface;

    public function getSort(): int;

    public function setSort(int $sort): ResearchInterface;

    public function getRumpId(): int;

    public function setRumpId(int $rumpId): ResearchInterface;

    public function getDatabaseEntryIds(): array;

    public function setDatabaseEntryIds(array $databaseEntryIds): ResearchInterface;

    public function getPoints(): int;

    public function setPoints(int $points): ResearchInterface;

    public function getGoodId(): int;

    public function setGoodId(int $good_id): ResearchInterface;

    public function getUpperPlanetLimit(): int;

    public function setUpperPlanetLimit(int $upperPlanetLimit): ResearchInterface;

    public function getUpperMoonLimit(): int;

    public function setUpperMoonLimit(int $upperMoonLimit): ResearchInterface;

    public function getGood(): CommodityInterface;

    public function getRewardBuildplanId(): ?int;

    public function getRewardBuildplan(): ?ShipBuildplanInterface;

    public function getNeededAward(): ?int;
}
