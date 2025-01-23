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

    /**
     * @return array<int>
     */
    public function getDatabaseEntryIds(): array;

    /**
     * @param array<int> $databaseEntryIds
     */
    public function setDatabaseEntryIds(array $databaseEntryIds): ResearchInterface;

    public function getPoints(): int;

    public function setPoints(int $points): ResearchInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): ResearchInterface;

    public function getUpperPlanetLimit(): int;

    public function getUpperMoonLimit(): int;

    public function getUpperAsteroidLimit(): int;

    public function getCommodity(): CommodityInterface;

    public function getRewardBuildplanId(): ?int;

    public function getRewardBuildplan(): ?SpacecraftBuildplanInterface;

    public function getAward(): ?AwardInterface;

    public function getNeededAwardId(): ?int;
}
