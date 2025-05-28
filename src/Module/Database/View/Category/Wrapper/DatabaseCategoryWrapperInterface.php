<?php

namespace Stu\Module\Database\View\Category\Wrapper;

interface DatabaseCategoryWrapperInterface
{
    public function isCategoryStarSystems(): bool;

    public function isCategoryStarSystemTypes(): bool;

    public function isCategoryTradePosts(): bool;

    public function isCategoryColonyClasses(): bool;

    public function isCategoryRumpTypes(): bool;

    public function isCategoryRegion(): bool;

    public function displayDefaultList(): bool;

    /** @return array<int, DatabaseCategoryEntryWrapperInterface> */
    public function getEntries(): array;

    public function getId(): int;
}
