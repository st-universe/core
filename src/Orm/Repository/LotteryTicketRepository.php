<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\LotteryTicketInterface;

final class LotteryTicketRepository extends EntityRepository implements LotteryTicketRepositoryInterface
{
    public function prototype(): LotteryTicketInterface
    {
        return new LotteryTicket();
    }

    public function save(LotteryTicketInterface $lotteryticket): void
    {
        $em = $this->getEntityManager();

        $em->persist($lotteryticket);
    }

    public function getAmountByPeriod(string $period): int
    {
        return $this->count([
            'period' => $period
        ]);
    }

    public function getAmountByPeriodAndUser(string $period, int $userId): int
    {
        return $this->count([
            'period' => $period,
            'user_id' => $userId
        ]);
    }

    public function getByPeriod(string $period): array
    {
        return $this->findBy(['period' => $period, 'is_winner' => NULL]);
    }
}
