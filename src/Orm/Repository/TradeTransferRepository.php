<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\TradeTransfer;
use Stu\Orm\Entity\TradeTransferInterface;

/**
 * @extends EntityRepository<TradeTransfer>
 */
final class TradeTransferRepository extends EntityRepository implements TradeTransferRepositoryInterface
{
    #[Override]
    public function prototype(): TradeTransferInterface
    {
        return new TradeTransfer();
    }

    #[Override]
    public function save(TradeTransferInterface $tradeTransfer): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradeTransfer);
    }

    #[Override]
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
                'date' => time() - TimeConstants::ONE_DAY_IN_SECONDS
            ])
            ->getSingleScalarResult();
    }
}
