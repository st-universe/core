<?php

namespace Stu\Orm\Entity;

use Stu\Module\Ship\Lib\EntityWithAstroEntryInterface;

interface MapRegionInterface extends EntityWithAstroEntryInterface
{
    public function getId(): int;

    public function getDescription(): string;

    public function setDescription(string $description): MapRegionInterface;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): MapRegionInterface;

    public function getLayers(): ?string;

    public function setLayers(?string $layers): MapRegionInterface;
}
