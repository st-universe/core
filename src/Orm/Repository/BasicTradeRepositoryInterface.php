<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\BasicTradeInterface;

/**
 * @extends ObjectRepository<BasicTrade>
 *
 * @method null|BasicTradeInterface find(integer $id)
 */
interface BasicTradeRepositoryInterface extends ObjectRepository
{
    public function prototype(): BasicTradeInterface;

    public function save(BasicTradeInterface $basicTrade): void;

    /**
     * @return array<BasicTradeInterface>
     */
    public function getBasicTrades(int $userId): array;

    public function getByUniqId(string $uniqId): ?BasicTradeInterface;

    public function isNewest(BasicTradeInterface $basicTrade): bool;

    /**
     * @return array<BasicTradeInterface>
     */
    public function getLatestRates(BasicTradeInterface $basicTrade): array;

    public function truncateAllBasicTrades(): void;
}
