<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Wrapper;

use Override;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;

final class DatabaseCategoryWrapper implements DatabaseCategoryWrapperInterface
{
    public function __construct(
        private DatabaseCategoryWrapperFactoryInterface $databaseCategoryWrapperFactory,
        private DatabaseCategoryInterface $databaseCategory,
        private UserInterface $user,
        private DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private ?int $layer = null
    ) {}

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
    public function isCategoryRegion(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_REGION;
    }

    #[Override]
    public function displayDefaultList(): bool
    {
        return !$this->isCategoryStarSystems()
            && !$this->isCategoryTradePosts()
            && !$this->isCategoryStarSystemTypes()
            && !$this->isCategoryRumpTypes()
            && !$this->isCategoryColonyClasses()
            && !$this->isCategoryRegion();
    }

    #[Override]
    public function getEntries(): array
    {
        if ($this->isCategoryStarSystems()) {
            $entries = $this->databaseEntryRepository->getStarSystemEntriesByLayer(
                $this->databaseCategory->getId(),
                $this->layer
            );
        } elseif ($this->isCategoryRegion()) {
            $entries = $this->databaseEntryRepository->getRegionEntriesByLayer(
                $this->databaseCategory->getId(),
                $this->layer
            );
        } elseif ($this->isCategoryTradePosts()) {
            $entries = $this->databaseEntryRepository->getTradePostEntriesByLayer(
                $this->databaseCategory->getId(),
                $this->layer
            );
        } else {
            $entries = $this->databaseCategory->getEntries();
        }

        return array_map(
            fn(DatabaseEntryInterface $entry): DatabaseCategoryEntryWrapperInterface =>
            $this->databaseCategoryWrapperFactory->createDatabaseCategoryEntryWrapper($entry, $this->user),
            $entries
        );
    }

    #[Override]
    public function getId(): int
    {
        return $this->databaseCategory->getId();
    }
}
