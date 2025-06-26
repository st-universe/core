<?php

namespace Stu\Lib\Transfer\Storage;

use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\Commodity;

interface StorageManagerInterface
{
    public function lowerStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount): void;

    public function upperStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount): void;
}
