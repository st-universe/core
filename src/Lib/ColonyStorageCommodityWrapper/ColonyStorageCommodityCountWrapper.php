<?php

declare(strict_types=1);

namespace Stu\Lib\ColonyStorageCommodityWrapper;

use Stu\Orm\Entity\StorageInterface;

class ColonyStorageCommodityCountWrapper
{
    const CHECK_ONLY = 'x';

    /** @var array<int, StorageInterface> */
    private $storage;
    /** @var int */
    private $commodityId;

    /**
     * @param array<int, StorageInterface> $storage
     * @param int $commodityId
     */
    function __construct(&$storage, $commodityId)
    {
        $this->storage = $storage;
        $this->commodityId = $commodityId;
    }

    /**
     * @param int $count
     *
     * @return bool
     */
    public function __get($count)
    {
        if (!isset($this->storage[$this->commodityId])) {
            return false;
        }
        if ($count == self::CHECK_ONLY) {
            return true;
        }
        if ($this->storage[$this->commodityId]->getAmount() < $count) {
            return false;
        }
        return true;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        if (!isset($this->storage[$this->commodityId])) {
            return 0;
        }
        return $this->storage[$this->commodityId]->getAmount();
    }

    public function __call($name, $arg)
    {
        return $this->__get($name);
    }

}
