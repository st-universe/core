<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\LotteryTicketInterface;

/**
 * @extends EntityRepository<LotteryTicket>
 */
final class LotteryTicketRepository extends EntityRepository implements LotteryTicketRepositoryInterface
{
    #[Override]
    public function prototype(): LotteryTicketInterface
    {
        return new LotteryTicket();
    }

    #[Override]
    public function save(LotteryTicketInterface $lotteryticket): void
    {
        $em = $this->getEntityManager();

        $em->persist($lotteryticket);
    }

    #[Override]
    public function getAmountByPeriod(string $period): int
    {
        return $this->count([
            'period' => $period
        ]);
    }

    #[Override]
    public function getAmountByPeriodAndUser(string $period, int $userId): int
    {
        return $this->count([
            'period' => $period,
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function getByPeriod(string $period): array
    {
        return $this->findBy(['period' => $period, 'is_winner' => null]);
    }

    #[Override]
    public function getLotteryHistory(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('period', 'period');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT lt.period AS period, count(lt.id) AS amount
                FROM stu_lottery_ticket lt
                WHERE lt.is_winner IS NOT NULL
                GROUP BY lt.period
                ORDER BY lt.period DESC
                LIMIT 24',
                $rsm
            )
            ->getResult();
    }

    #[Override]
    public function truncateAllLotteryTickets(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s lt',
                LotteryTicket::class
            )
        )->execute();
    }
}
