<?php

namespace Stu\Orm\Entity;

interface DatabaseCategoryInterface
{
    public function getId(): int;

    public function setDescription(string $description): DatabaseCategoryInterface;

    public function getDescription(): string;

    public function setPoints(int $points): DatabaseCategoryInterface;

    public function getPoints(): int;

    public function setType(int $type): DatabaseCategoryInterface;

    public function getType(): int;

    public function setSort(int $sort): DatabaseCategoryInterface;

    public function getSort(): int;

    public function getPrestige(): int;

    /**
     * Returns a list of associated database entries
     * @return DatabaseEntryInterface[]
     */
    public function getEntries(): array;
}
