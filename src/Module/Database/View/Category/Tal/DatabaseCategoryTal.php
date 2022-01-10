<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;

final class DatabaseCategoryTal implements DatabaseCategoryTalInterface
{
    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private DatabaseCategoryInterface $databaseCategory;

    private UserInterface $user;

    public function __construct(
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        DatabaseCategoryInterface $databaseCategory,
        UserInterface $user
    ) {
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->databaseCategory = $databaseCategory;
        $this->user = $user;
    }

    public function isCategoryStarSystemTypes(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STAR_SYSTEM_TYPE;
    }

    public function isCategoryStarSystems(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STARSYSTEM;
    }

    public function isCategoryTradePosts(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_TRADEPOST;
    }

    public function isCategoryPlanetTypes(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_PLANET_TYPE;
    }

    public function isCategoryShipTypes(): bool
    {
        return $this->databaseCategory->getId() == DatabaseCategoryTypeEnum::DATABASE_CATEGORY_SHIPRUMP;
    }

    public function displayDefaultList(): bool
    {
        return !$this->isCategoryStarSystems()
            && !$this->isCategoryTradePosts()
            && !$this->isCategoryStarSystemTypes()
            && !$this->isCategoryShipTypes()
            && !$this->isCategoryPlanetTypes();
    }

    public function getEntries(): array
    {
        return array_map(
            function (DatabaseEntryInterface $entry): DatabaseCategoryEntryTalInterface {
                return $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($entry, $this->user);
            },
            $this->databaseCategory->getEntries()
        );
    }

    public function getId(): int
    {
        return $this->databaseCategory->getId();
    }
}
