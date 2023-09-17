<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Create ui items related for database views
 */
final class DatabaseUiFactory implements DatabaseUiFactoryInterface
{
    private CommodityRepositoryInterface $commodityRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->commodityRepository = $commodityRepository;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->userRepository = $userRepository;
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

    public function createDatabaseTopActivTradePost(
        array $item
    ): DatabaseTopActivTradePost {
        return new DatabaseTopActivTradePost(
            $this->userRepository,
            $item
        );
    }

    public function createDatabaseTopListCrew(
        array $item
    ): DatabaseTopListCrew {
        return new DatabaseTopListCrew(
            $this->userRepository,
            $item
        );
    }

    public function createDatabaseTopListDiscoverer(
        array $item
    ): DatabaseTopListDiscover {
        return new DatabaseTopListDiscover(
            $this->userRepository,
            $item
        );
    }

    public function createDatabaseTopListFlights(
        array $item
    ): DatabaseTopListFlights {
        return new DatabaseTopListFlights(
            $this->userRepository,
            $item
        );
    }

    public function createDatabaseTopListArchitects(
        int $userId,
        string $points
    ): DatabaseTopListArchitects {
        return new DatabaseTopListArchitects(
            $this->userRepository,
            $userId,
            $points
        );
    }
}
