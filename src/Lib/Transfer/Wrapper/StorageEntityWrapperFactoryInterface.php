<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Stu\Lib\Transfer\EntityWithStorageInterface;

interface StorageEntityWrapperFactoryInterface
{

    public function wrapStorageEntity(EntityWithStorageInterface $entityWithStorage): StorageEntityWrapperInterface;
}
