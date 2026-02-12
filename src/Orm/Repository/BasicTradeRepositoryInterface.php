<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BasicTrade;

/**
 * @extends ObjectRepository<BasicTrade>
 *
 * @method null|BasicTrade find(integer $id)
 */
interface BasicTradeRepositoryInterface extends ObjectRepository
{
    public function prototype(): BasicTrade;

    public function save(BasicTrade $basicTrade): void;

    /**
     * @return array<BasicTrade>
     */
    public function getBasicTrades(int $userId): array;

    public function getByUniqId(string $uniqId): ?BasicTrade;

    public function isNewest(BasicTrade $basicTrade): bool;

    /**
     * @return array<BasicTrade>
     */
    public function getLatestRates(BasicTrade $basicTrade): array;

    /**
     * @return array<BasicTrade>
     */
    public function getLatestRatesByAmount(BasicTrade $basicTrade, int $amount): array;

    public function getTradeCount(BasicTrade $basicTrade): int;
}
