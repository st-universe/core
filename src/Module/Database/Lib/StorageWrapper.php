<?php

namespace Stu\Module\Database\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class StorageWrapper
{
    private CommodityRepositoryInterface $commodityRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private int $commodityId;

    private int $amount;

    private ?int $entityId;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        TradePostRepositoryInterface $tradePostRepository,
        int $commodityId,
        int $amount,
        ?int $entityId
    ) {
        $this->commodityId = $commodityId;
        $this->amount = $amount;
        $this->commodityRepository = $commodityRepository;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->entityId = $entityId;
    }

    public function getCommodityId(): int
    {
        return $this->commodityId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCommodity(): ?CommodityInterface
    {
        return $this->commodityRepository->find($this->commodityId);
    }

    public function getShip(): ?ShipInterface
    {
        return $this->shipRepository->find((int) $this->entityId);
    }

    public function getColony(): ?ColonyInterface
    {
        return $this->colonyRepository->find((int) $this->entityId);
    }

    public function getTradepost(): ?TradePostInterface
    {
        return $this->tradePostRepository->find((int) $this->entityId);
    }
}
