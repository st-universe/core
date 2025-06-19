<?php

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;

interface ShipRumpCategoryInterface
{
    public function getId(): SpacecraftRumpCategoryEnum;

    public function getName(): string;

    public function setName(string $name): ShipRumpCategoryInterface;

    public function getDatabaseId(): int;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpCategoryInterface;

    public function getType(): SpacecraftTypeEnum;
}
