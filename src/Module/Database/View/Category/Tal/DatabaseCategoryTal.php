<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Override;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;

final class DatabaseCategoryTal implements DatabaseCategoryTalInterface
{
    public function __construct(private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory, private DatabaseCategoryInterface $databaseCategory, private UserInterface $user)
    {
    }

    #[Override]
    public function isCategoryStarSystemTypes(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STAR_SYSTEM_TYPE;
    }

    #[Override]
    public function isCategoryStarSystems(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STARSYSTEM;
    }

    #[Override]
    public function isCategoryTradePosts(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_TRADEPOST;
    }

    #[Override]
    public function isCategoryColonyClasses(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_COLONY_CLASS;
    }

    #[Override]
    public function isCategoryRumpTypes(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_SHIPRUMP
            || $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STATIONRUMP;
    }

    #[Override]
    public function displayDefaultList(): bool
    {
        return !$this->isCategoryStarSystems()
            && !$this->isCategoryTradePosts()
            && !$this->isCategoryStarSystemTypes()
            && !$this->isCategoryRumpTypes()
            && !$this->isCategoryColonyClasses();
    }

    #[Override]
    public function getEntries(): array
    {
        return array_map(
            fn (DatabaseEntryInterface $entry): DatabaseCategoryEntryTalInterface => $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($entry, $this->user),
            $this->databaseCategory->getEntries()
        );
    }

    #[Override]
    public function getId(): int
    {
        return $this->databaseCategory->getId();
    }
}
