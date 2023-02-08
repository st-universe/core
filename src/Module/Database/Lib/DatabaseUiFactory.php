<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

/**
 * Create ui items related for database views
 */
final class DatabaseUiFactory implements DatabaseUiFactoryInterface
{
    private CommodityRepositoryInterface $commodityRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->commodityRepository = $commodityRepository;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function createStorageWrapper(
        int $commodityId,
        int $amount,
        ?int $entityId = null
    ): StorageWrapper {
        return new StorageWrapper(
            $this->commodityRepository,
            $this->shipRepository,
            $this->colonyRepository,
            $this->tradePostRepository,
            $commodityId,
            $amount,
            $entityId
        );
    }
}
