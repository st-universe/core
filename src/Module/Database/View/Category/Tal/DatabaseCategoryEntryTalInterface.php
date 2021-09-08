<?php

namespace Stu\Module\Database\View\Category\Tal;

interface DatabaseCategoryEntryTalInterface
{
    public function getObject();

    public function wasDiscovered(): bool;

    public function getId(): int;

    public function getObjectId(): int;

    public function getDescription(): string;

    public function getDiscoveryDate(): int;
}
