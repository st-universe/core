<?php

namespace Stu\Module\Database\View\Category\Tal;

use DatabaseUserData;

interface DatabaseCategoryEntryTalInterface
{
    public function getObject();

    public function wasDiscovered(): bool;

    public function getId(): int;

    public function getDescription(): string;

    public function getDiscoveryDate(): int;
}