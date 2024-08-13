<?php

namespace Stu\Orm\Entity;

interface ShipRumpCategoryInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): ShipRumpCategoryInterface;

    public function getDatabaseId(): int;

    //@deprecated
    public function getPoints(): int;

    public function setPoints(int $points): ShipRumpCategoryInterface;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpCategoryInterface;
}
