<?php

namespace Stu\Lib\StorageWrapper;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

class StorageWrapper
{

    private $commodityId;
    private $amount;

    function __construct(int $commodityId, int $amount)
    {
        $this->commodityId = $commodityId;
        $this->amount = $amount;
    }

    public function getCommodityId()
    {
        return $this->commodityId;
    }

    function getAmount()
    {
        return $this->amount;
    }

    public function getCommodity(): CommodityInterface
    {
        // @todo refactor
        global $container;

        return $container->get(CommodityRepositoryInterface::class)->find((int) $this->getCommodityId());
    }

}
