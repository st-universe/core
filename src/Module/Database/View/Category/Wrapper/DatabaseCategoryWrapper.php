<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Wrapper;

use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;

final class DatabaseCategoryWrapper implements DatabaseCategoryWrapperInterface
{
    public function __construct(
        private DatabaseCategoryWrapperFactoryInterface $databaseCategoryWrapperFactory,
        private DatabaseCategory $databaseCategory,
        private User $user,
        private DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private ?int $layer = null
    ) {}

    #[\Override]
    public function isCategoryStarSystemTypes(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::STAR_SYSTEM_TYPE->value;
    }

    #[\Override]
    public function isCategoryStarSystems(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::STARSYSTEM->value;
    }

    #[\Override]
    public function isCategoryTradePosts(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::TRADEPOST->value;
    }

    #[\Override]
    public function isCategoryColonyClasses(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::COLONY_CLASS->value;
    }

    #[\Override]
    public function isCategoryRumpTypes(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::SHIPRUMP->value
            || $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::STATIONRUMP->value;
    }

    #[\Override]
    public function isCategoryRegion(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::REGION->value;
    }

    #[\Override]
    public function displayDefaultList(): bool
    {
        return !$this->isCategoryStarSystems()
            && !$this->isCategoryTradePosts()
            && !$this->isCategoryStarSystemTypes()
            && !$this->isCategoryRumpTypes()
            && !$this->isCategoryColonyClasses()
            && !$this->isCategoryRegion();
    }

    #[\Override]
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
            fn(DatabaseEntry $entry): DatabaseCategoryEntryWrapperInterface =>
            $this->databaseCategoryWrapperFactory->createDatabaseCategoryEntryWrapper($entry, $this->user),
            $entries
        );
    }

    #[\Override]
    public function getId(): int
    {
        return $this->databaseCategory->getId();
    }
}
