<?php

namespace Stu\Orm\Entity;

use DatabaseType;
use DatabaseUserData;

interface DatabaseEntryInterface
{
    public function getId(): int;

    public function setDescription(string $description): DatabaseEntryInterface;

    public function getDescription(): string;

    public function setData(string $data): DatabaseEntryInterface;

    public function getData(): string;

    public function setCategoryId(int $categoryId): DatabaseEntryInterface;

    public function getCategoryId(): int;

    public function setType(int $type): DatabaseEntryInterface;

    public function getType(): int;

    public function setSort(int $sort): DatabaseEntryInterface;

    public function getSort(): int;

    public function setObjectId(int $objectId): DatabaseEntryInterface;

    public function getObjectId(): int;

    public function getObject();

    public function getTypeObject(): DatabaseType;

    public function isDiscoveredByCurrentUser(): bool;

    public function getDBUserObject(): ?DatabaseUserData;

    public function getCategory(): DatabaseCategoryInterface;
}
