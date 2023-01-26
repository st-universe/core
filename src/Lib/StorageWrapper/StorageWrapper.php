<?php

namespace Stu\Lib\StorageWrapper;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;


class StorageWrapper
{
    /** @var int  */
    private $commodityId;

    /** @var int  */
    private $amount;

    /** @var int  */
    private $entityId;

    function __construct(int $commodityId, int $amount)
    {
        $this->commodityId = $commodityId;
        $this->amount = $amount;
    }

    public function getCommodityId(): int
    {
        return $this->commodityId;
    }

    function getAmount(): int
    {
        return $this->amount;
    }

    public function addAmount(int $amount): void
    {
        $this->amount += $amount;
    }

    public function setEntityId(int $id): void
    {
        $this->entityId = $id;
    }

    public function getCommodity(): CommodityInterface
    {
        // @todo refactor
        global $container;

        return $container->get(CommodityRepositoryInterface::class)->find((int) $this->getCommodityId());
    }

    public function getShip() : ShipInterface
    {
        global $container;

        return $container->get(ShipRepositoryInterface::class)->find((int) $this->entityId);
    }

    public function getColony() : ColonyInterface
    {
        global $container;

        return $container->get(ColonyRepositoryInterface::class)->find((int) $this->entityId);
    }

    public function getTradepost() : TradePostInterface
    {
        global $container;

        return $container->get(TradePostRepositoryInterface::class)->find((int) $this->entityId);
    }

}
