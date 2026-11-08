<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Storage;

use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\Commodity;

interface StorageManagerInterface
{
    public function lowerStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount): void;

    /** @return array<int, int> */
    public function removeStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount, ?int $quality = null): array;

    /** @param array<int, int> $qualities */
    public function addStorage(EntityWithStorageInterface $entity, Commodity $commodity, array $qualities): void;

    public function transferStorage(EntityWithStorageInterface $source, EntityWithStorageInterface $target, Commodity $commodity, int $amount, ?int $quality = null): void;

    public function upperStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount): void;
}
