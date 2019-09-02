<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\TradeTransferInterface;

interface TradeTransferRepositoryInterface
{
    public function prototype(): TradeTransferInterface;

    public function save(TradeTransferInterface $tradeTransfer): void;

    public function getSumByPostAndUser(int $tradePostId, int $userId): int;
}