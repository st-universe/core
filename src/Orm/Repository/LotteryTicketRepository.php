<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\LotteryTicketInterface;

/**
 * @extends EntityRepository<LotteryTicket>
 */
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

    public function getLotteryHistory(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('period', 'period', 'string');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createQuery(sprintf(
            'SELECT lt.period as period, count(lt.id) as amount
            FROM %s lt
            WHERE lt.is_winner IS not null
            GROUP BY lt.period
            ORDER BY lt.period DESC',
            LotteryTicket::class
        ))->setResultSetMapping($rsm)->setMaxResults(20)
            ->getResult();
    }
}
