<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Create ui items related for database views
 */
final class DatabaseUiFactory implements DatabaseUiFactoryInterface
{
    public function __construct(
        private CommodityRepositoryInterface $commodityRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private UserRepositoryInterface $userRepository,
        private TradePostRepositoryInterface $tradePostRepository
    ) {}

    #[\Override]
    public function createStorageWrapper(
        int $commodityId,
        int $amount,
        ?int $entityId = null
    ): StorageWrapper {
        return new StorageWrapper(
            $this->commodityRepository,
            $this->spacecraftRepository,
            $this->colonyRepository,
            $this->tradePostRepository,
            $commodityId,
            $amount,
            $entityId
        );
    }

    #[\Override]
    public function createDatabaseTopActivTradePost(
        array $item
    ): DatabaseTopActivTradePost {
        return new DatabaseTopActivTradePost(
            $this->userRepository,
            $item
        );
    }

    #[\Override]
    public function createDatabaseTopListCrew(
        array $item
    ): DatabaseTopListCrew {
        return new DatabaseTopListCrew(
            $this->userRepository,
            $item
        );
    }

    #[\Override]
    public function createDatabaseTopListWithPoints(
        int $userId,
        string $points,
        ?int $time = null
    ): DatabaseTopListWithPoints {
        return new DatabaseTopListWithPoints(
            $this->userRepository,
            $userId,
            $points,
            $time
        );
    }

    #[\Override]
    public function createDatabaseTopListFlights(
        array $item
    ): DatabaseTopListFlights {
        return new DatabaseTopListFlights(
            $this->userRepository,
            $item
        );
    }

    #[\Override]
    public function createDatabaseTopListWithColorGradient(
        int $userId,
        string $gradientColor
    ): DatabaseTopListWithColorGradient {
        return new DatabaseTopListWithColorGradient(
            $this->userRepository,
            $userId,
            $gradientColor
        );
    }
}
