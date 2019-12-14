<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeTransferInterface;

/**
 * @method null|TradeTransferInterface find(integer $id)
 */
interface TradeTransferRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeTransferInterface;

    public function save(TradeTransferInterface $tradeTransfer): void;

    public function getSumByPostAndUser(int $tradePostId, int $userId): int;
}