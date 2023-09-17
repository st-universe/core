<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

interface DatabaseUiFactoryInterface
{
    public function createStorageWrapper(
        int $commodityId,
        int $amount,
        ?int $entityId = null
    ): StorageWrapper;

    /**
     * @param array{id: int, name: string, transactions: int} $item
     */
    public function createDatabaseTopActivTradePost(
        array $item
    ): DatabaseTopActivTradePost;

    /**
     * @param array{user_id: int, race: int, crewc: int} $item
     */
    public function createDatabaseTopListCrew(
        array $item
    ): DatabaseTopListCrew;

    /**
     * @param array{user_id: int, points: int} $item
     */
    public function createDatabaseTopListDiscoverer(
        array $item
    ): DatabaseTopListDiscover;

    /**
     * @param array{user_id: int, sc: int, race: null|int, shipc: int} $item
     */
    public function createDatabaseTopListFlights(
        array $item
    ): DatabaseTopListFlights;

    public function createDatabaseTopListArchitects(
        int $userId,
        string $points
    ): DatabaseTopListArchitects;
}
