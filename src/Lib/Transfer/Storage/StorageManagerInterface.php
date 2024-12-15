<?php

namespace Stu\Lib\Transfer\Storage;

use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\CommodityInterface;

interface StorageManagerInterface
{
    public function lowerStorage(EntityWithStorageInterface $entity, CommodityInterface $commodity, int $amount): void;

    public function upperStorage(EntityWithStorageInterface $entity, CommodityInterface $commodity, int $amount): void;
}
