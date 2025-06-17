<?php

namespace Stu\Orm\Entity;

interface DatabaseEntryInterface
{
    public function getId(): int;

    public function setDescription(string $description): DatabaseEntryInterface;

    public function getDescription(): string;

    public function setData(string $data): DatabaseEntryInterface;

    public function getData(): string;

    public function setCategory(DatabaseCategoryInterface $category): DatabaseEntryInterface;

    public function getCategory(): DatabaseCategoryInterface;

    public function setSort(int $sort): DatabaseEntryInterface;

    public function getSort(): int;

    public function setObjectId(int $objectId): DatabaseEntryInterface;

    public function getObjectId(): int;

    public function getTypeObject(): DatabaseTypeInterface;

    public function setTypeObject(DatabaseTypeInterface $typeObject): DatabaseEntryInterface;

    public function getCategoryId(): int;

    public function getTypeId(): int;

    public function getLayerId(): ?int;

    public function setLayerId(?int $layerId): DatabaseEntryInterface;
}
