<?php

namespace Stu\Module\Database\Lib;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class StorageWrapper
{
    public function __construct(
        private CommodityRepositoryInterface $commodityRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private int $commodityId,
        private int $amount,
        private ?int $entityId
    ) {}

    public function getCommodityId(): int
    {
        return $this->commodityId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCommodity(): ?Commodity
    {
        return $this->commodityRepository->find($this->commodityId);
    }

    public function getSpacecraft(): ?Spacecraft
    {
        return $this->spacecraftRepository->find((int) $this->entityId);
    }

    public function getColony(): ?Colony
    {
        return $this->colonyRepository->find((int) $this->entityId);
    }

    public function getTradepost(): ?TradePost
    {
        return $this->tradePostRepository->find((int) $this->entityId);
    }
}
