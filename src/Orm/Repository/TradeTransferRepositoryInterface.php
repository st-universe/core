<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeTransfer;

/**
 * @extends ObjectRepository<TradeTransfer>
 *
 * @method null|TradeTransfer find(integer $id)
 */
interface TradeTransferRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeTransfer;

    public function save(TradeTransfer $tradeTransfer): void;

    public function getSumByPostAndUser(int $tradePostId, int $userId): int;
}
