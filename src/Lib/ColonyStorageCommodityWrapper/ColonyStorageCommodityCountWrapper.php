<?php

declare(strict_types=1);

namespace Stu\Lib\ColonyStorageCommodityWrapper;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\StorageInterface;

class ColonyStorageCommodityCountWrapper
{
    public const CHECK_ONLY = 'x';

    /** @var Collection<int, StorageInterface> */
    private Collection $storage;

    private int $commodityId;

    /**
     * @param Collection<int, StorageInterface> $storage
     */
    public function __construct(Collection $storage, int $commodityId)
    {
        $this->storage = $storage;
        $this->commodityId = $commodityId;
    }

    /**
     * @param scalar $count
     */
    public function __get($count): bool
    {
        $count = (int) $count;
        if (!isset($this->storage[$this->commodityId])) {
            return false;
        }
        if ($count == self::CHECK_ONLY) {
            return true;
        }
        return $this->storage[$this->commodityId]->getAmount() >= $count;
    }

    public function getAmount(): int
    {
        if (!isset($this->storage[$this->commodityId])) {
            return 0;
        }
        return $this->storage[$this->commodityId]->getAmount();
    }

    /**
     * @param string $name
     * @param array<mixed> $arg
     */
    public function __call($name, $arg): bool
    {
        return $this->__get($name);
    }
}
