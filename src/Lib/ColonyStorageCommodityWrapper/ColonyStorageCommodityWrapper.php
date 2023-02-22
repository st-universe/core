<?php

namespace Stu\Lib\ColonyStorageCommodityWrapper;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\StorageInterface;

class ColonyStorageCommodityWrapper
{
    /** @var Collection<int, StorageInterface> */
    private Collection $storage;

    /**
     * @param Collection<int, StorageInterface> $storage
     */
    public function __construct(Collection $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param scalar $commodityId
     */
    public function __get($commodityId): ColonyStorageCommodityCountWrapper
    {
        return new ColonyStorageCommodityCountWrapper($this->storage, (int) $commodityId);
    }

    /**
     * @param string $name
     * @param array<mixed> $arg
     */
    public function __call($name, $arg): ColonyStorageCommodityCountWrapper
    {
        return $this->__get($name);
    }
}
