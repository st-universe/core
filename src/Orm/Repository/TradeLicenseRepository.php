<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Trade\TradeEnum;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePost;

/**
 * @extends EntityRepository<TradeLicense>
 */
final class TradeLicenseRepository extends EntityRepository implements TradeLicenseRepositoryInterface
{
    #[\Override]
    public function prototype(): TradeLicense
    {
        return new TradeLicense();
    }

    #[\Override]
    public function save(TradeLicense $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(TradeLicense $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[\Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s t WHERE t.user_id = :userId',
                    TradeLicense::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }

    #[\Override]
    public function truncateByUserAndTradepost(int $userId, int $tradePostId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s t WHERE t.user_id = :userId AND t.posts_id = :TradePostId',
                    TradeLicense::class
                )
            )
            ->setParameter('userId', $userId)
            ->setParameter('TradePostId', $tradePostId)
            ->execute();
    }

    #[\Override]
    public function getByTradePost(int $tradePostId): array
    {
        return $this->findBy([
            'posts_id' => $tradePostId
        ]);
    }

    #[\Override]
    public function getByTradePostAndNotExpired(int $tradePostId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tl FROM %s tl WHERE tl.posts_id = :tradePostId AND tl.expired > :actualTime',
                    TradeLicense::class
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId,
                'actualTime' => time()
            ])
            ->getResult();
    }

    #[\Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            [
                'user_id' => $userId
            ],
            ['posts_id' => 'asc']
        );
    }

    #[\Override]
    public function getLicensesCountbyUser(int $userId): array
    {
        $time = time();
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tl FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime',
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => $time
            ])
            ->getResult();
    }

    #[\Override]
    public function getAmountByUser(int $userId): int
    {
        return $this->count([
            'user_id' => $userId
        ]);
    }

    #[\Override]
    public function hasFergLicense(int $userId): bool
    {
        return $this->getLatestActiveLicenseByUserAndTradePost(
            $userId,
            TradeEnum::DEALS_FERG_TRADEPOST_ID
        ) !== null;
    }

    #[\Override]
    public function hasLicenseByUserAndTradePost(int $userId, int $tradePostId): bool
    {
        return $this->getLatestActiveLicenseByUserAndTradePost($userId, $tradePostId) !== null;
    }

    #[\Override]
    public function getLatestActiveLicenseByUserAndTradePost(int $userId, int $tradePostId): ?TradeLicense
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tl
                    FROM %s tl
                    WHERE tl.user_id = :userId
                        AND tl.posts_id = :tradePostId
                        AND tl.expired > :actualTime
                    ORDER BY tl.id DESC',
                    TradeLicense::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'userId' => $userId,
                'tradePostId' => $tradePostId,
                'actualTime' => time()
            ])
            ->getOneOrNullResult();
    }

    #[\Override]
    public function getAmountByTradePost(int $tradePostId): int
    {
        return $this->count([
            'posts_id' => $tradePostId
        ]);
    }

    #[\Override]
    public function hasLicenseByUserAndNetwork(int $userId, int $tradeNetworkId): bool
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(tl.id)
                    FROM %s tl
                    WHERE tl.user_id = :userId
                        AND tl.posts_id IN (
                            SELECT tp.id FROM %s tp WHERE tp.trade_network = :tradeNetworkId
                            )',
                    TradeLicense::class,
                    TradePost::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'tradeNetworkId' => $tradeNetworkId
            ])
            ->getSingleScalarResult() > 0;
    }

    #[\Override]
    public function getLicensesExpiredInLessThan(int $days): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tl FROM %s tl WHERE tl.expired < :threshold',
                    TradeLicense::class
                )
            )
            ->setParameters([
                'threshold' => time() + TimeConstants::ONE_DAY_IN_SECONDS * $days
            ])
            ->getResult();
    }

    #[\Override]
    public function getExpiredLicenses(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tl FROM %s tl INDEX BY tl.id
                    WHERE tl.expired < :actualTime',
                    TradeLicense::class
                )
            )
            ->setParameters([
                'actualTime' => time()
            ])
            ->getResult();
    }
}
