<?php

namespace Stu\Orm\Entity;

interface MapRegionInterface
{
    public function getId(): int;

    public function getDescription(): string;

    public function setDescription(string $description): MapRegionInterface;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): MapRegionInterface;

    public function isAdministrated(): bool;

    public function setIsAdministrated(bool $isAdministrated): MapRegionInterface;
}
