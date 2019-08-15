<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;

final class DatabaseCategoryTal implements DatabaseCategoryTalInterface
{
    private $databaseCategoryTalFactory;

    private $databaseCategory;

    public function __construct(
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        DatabaseCategoryInterface $databaseCategory
    )
    {
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->databaseCategory = $databaseCategory;
    }

    public function isCategoryStarSystems(): bool
    {
        return $this->databaseCategory->getId() == DATABASE_CATEGORY_STARSYSTEM;
    }

    public function isCategoryTradePosts(): bool
    {
        return $this->databaseCategory->getId() == DATABASE_CATEGORY_TRADEPOST;
    }

    public function displayDefaultList(): bool
    {
        return !$this->isCategoryStarSystems() && !$this->isCategoryTradePosts();
    }

    public function getEntries(): array
    {
        return array_map(
            function (DatabaseEntryInterface $entry): DatabaseCategoryEntryTalInterface {
                return $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($entry);
            },
            $this->databaseCategory->getEntries()
        );
    }

    public function getId(): int {
        return $this->databaseCategory->getId();
    }
}