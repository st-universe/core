<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

interface DatabaseUiFactoryInterface
{
    public function createStorageWrapper(
        int $commodityId,
        int $amount,
        ?int $entityId = null
    ): StorageWrapper;
}
