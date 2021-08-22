<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TradeTransfer;
use Stu\Orm\Entity\TradeTransferInterface;

final class TradeTransferRepository extends EntityRepository implements TradeTransferRepositoryInterface
{
    public function prototype(): TradeTransferInterface
    {
        return new TradeTransfer();
    }

    public function save(TradeTransferInterface $tradeTransfer): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradeTransfer);
    }

    public function getSumByPostAndUser(int $tradePostId, int $userId): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT SUM(t.count) as amount FROM %s t WHERE t.posts_id = :tradePostId AND t.user_id = :userId AND t.date > :date',
                    TradeTransfer::class,
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId,
                'userId' => $userId,
                'date' => time() - 86400
            ])
            ->getSingleScalarResult();
    }
}
