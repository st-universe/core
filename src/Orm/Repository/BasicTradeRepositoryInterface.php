<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BasicTradeInterface;

/**
 * @method null|BasicTradeInterface find(integer $id)
 */
interface BasicTradeRepositoryInterface extends ObjectRepository
{
    public function prototype(): BasicTradeInterface;

    public function save(BasicTradeInterface $basicTrade): void;

    /**
     * @return BasicTradeInterface[]
     */
    public function getBasicTrades(int $userId): array;

    public function getByUniqId(int $uniqId): ?BasicTradeInterface;

    public function isNewest(BasicTradeInterface $basicTrade): bool;

    /**
     * @return BasicTradeInterface[]
     */
    public function getLatestRates(BasicTradeInterface $basicTrade): array;
}
