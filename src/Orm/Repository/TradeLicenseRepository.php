<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Trade\TradeEnum;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeLicenseInterface;
use Stu\Orm\Entity\TradePost;

/**
 * @extends EntityRepository<TradeLicense>
 */
final class TradeLicenseRepository extends EntityRepository implements TradeLicenseRepositoryInterface
{

    public function prototype(): TradeLicenseInterface
    {
        return new TradeLicense();
    }

    public function save(TradeLicenseInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(TradeLicenseInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

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

    public function getByTradePost(int $tradePostId): array
    {
        return $this->findBy([
            'posts_id' => $tradePostId
        ]);
    }

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

    public function getByUser(int $userId): array
    {
        return $this->findBy(
            [
                'user_id' => $userId
            ],
            ['posts_id' => 'asc']
        );
    }

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

    public function getAmountByUser(int $userId): int
    {
        return $this->count([
            'user_id' => $userId
        ]);
    }

    public function hasFergLicense(int $userId): bool
    {
        return $this->getLatestActiveLicenseByUserAndTradePost(
            $userId,
            TradeEnum::DEALS_FERG_TRADEPOST_ID
        ) !== null;
    }

    public function hasLicenseByUserAndTradePost(int $userId, int $tradePostId): bool
    {
        return $this->getLatestActiveLicenseByUserAndTradePost($userId, $tradePostId) !== null;
    }

    public function getLatestActiveLicenseByUserAndTradePost(int $userId, int $tradePostId): ?TradeLicenseInterface
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

    public function getAmountByTradePost(int $tradePostId): int
    {
        return $this->count([
            'posts_id' => $tradePostId
        ]);
    }

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
