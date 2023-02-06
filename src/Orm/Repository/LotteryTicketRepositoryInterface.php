<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\LotteryTicketInterface;

/**
 * @extends ObjectRepository<LotteryTicket>
 *
 * @method null|LotteryTicketInterface find(integer $id)
 */
interface LotteryTicketRepositoryInterface extends ObjectRepository
{
    public function prototype(): LotteryTicketInterface;

    public function save(LotteryTicketInterface $lotteryticket): void;

    public function getAmountByPeriod(string $period): int;

    public function getAmountByPeriodAndUser(string $period, int $userId): int;

    /**
     * @return LotteryTicketInterface[]
     */
    public function getByPeriod(string $period): array;

    /**
     * @return array<int, string>
     */
    public function getLotteryHistory(): array;
}
