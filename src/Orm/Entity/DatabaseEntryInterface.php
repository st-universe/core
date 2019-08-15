<?php

namespace Stu\Orm\Entity;

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

    public function setSort(int $sort): DatabaseEntryInterface;

    public function getSort(): int;

    public function setObjectId(int $objectId): DatabaseEntryInterface;

    public function getObjectId(): int;

    public function getObject();

    public function getTypeObject(): DatabaseTypeInterface;

    public function setTypeObject(DatabaseTypeInterface $type_object): DatabaseEntryInterface;

    public function isDiscoveredByCurrentUser(): bool;

    public function getDBUserObject(): ?DatabaseUserData;

    public function getCategory(): DatabaseCategoryInterface;
}
