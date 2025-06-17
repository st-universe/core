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
     *
     * @return array<int, DatabaseEntryInterface>
     */
    public function getEntries(): array;

    /**
     * Returns a list of associated category awards
     *
     * @return array<int, DatabaseCategoryAwardInterface>
     */
    public function getCategoryAwards(): array;
}
