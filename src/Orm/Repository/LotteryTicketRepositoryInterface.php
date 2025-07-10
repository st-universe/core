<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LotteryTicket;

/**
 * @extends ObjectRepository<LotteryTicket>
 *
 * @method null|LotteryTicket find(integer $id)
 */
interface LotteryTicketRepositoryInterface extends ObjectRepository
{
    public function prototype(): LotteryTicket;

    public function save(LotteryTicket $lotteryticket): void;

    public function getAmountByPeriod(string $period): int;

    public function getAmountByPeriodAndUser(string $period, int $userId): int;

    /**
     * @return list<LotteryTicket>
     */
    public function getByPeriod(string $period): array;

    /**
     * @return list<array{period: string, amount: int}>
     */
    public function getLotteryHistory(): array;
}
