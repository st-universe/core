<?php

namespace Stu\Module\Database\View\Category\Tal;

interface DatabaseCategoryTalInterface
{
    public function isCategoryStarSystems(): bool;

    public function isCategoryTradePosts(): bool;

    public function isCategoryPlanetTypes(): bool;

    public function displayDefaultList(): bool;

    public function getEntries(): array;

    public function getId(): int;
}
