<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LotteryTicketInterface;

/**
 * @method null|LotteryTicketInterface find(integer $id)
 */
interface LotteryTicketRepositoryInterface extends ObjectRepository
{
    public function prototype(): LotteryTicketInterface;

    public function save(LotteryTicketInterface $lotteryticket): void;

    public function getAmountByPeriod(string $period): int;

    /**
     * @return LotteryTicketInterface[]
     */
    public function getByPeriod(string $period): array;
}
