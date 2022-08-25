<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeLicenseInterface;
use Stu\Orm\Entity\TradeLicenceCreation;

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
        $em->flush();
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

    public function getByTradePost(int $tradePostId): array
    {
        return $this->findBy([
            'posts_id' => $tradePostId
        ]);
    }

    public function getByUser(int $userId): array
    {
        $time = time();
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime ORDER BY tl.posts_id ASC',
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

    public function hasLicenseByUserAndTradePost(int $userId, int $tradePostId): bool
    {
        return $this->count([
            'user_id' => $userId,
            'posts_id' => $tradePostId
        ]) > 0;
    }

    public function getAmountByTradePost(int $tradePostId): int
    {
        return $this->count([
            'posts_id' => $tradePostId
        ]);
    }

    public function hasLicenseByUserAndNetwork(int $userId, int $tradeNetworkId): bool
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');
        $time = time();

        return (int) $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COUNT(c.id) as amount FROM stu_trade_licences c WHERE c.user_id = :userId AND c.expired > :actime AND c.posts_id IN (
                    SELECT id FROM stu_trade_posts WHERE trade_network = :tradeNetworkId
                )',
                $rsm
            )
            ->setParameters([
                'userId' => $userId,
                'tradeNetworkId' => $tradeNetworkId,
                'actime' => $time
            ])
            ->getSingleScalarResult() > 0;
    }

    public function getExpiredTime(int $trade_post): int
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tlc.expired FROM %s tlc WHERE tlc.posts_id = :trade_post ORDER BY tlc.id DESC',
                    TradeLicense::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'trade_post' => $trade_post
            ])
            ->getSingleScalarResult();
    }

    public function getExpiredByTradepost(int $tradepost): int
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tlc.days FROM %s tlc WHERE tlc.posts_id = :trade_post ORDER BY tlc.id DESC',
                    TradeLicenceCreation::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'trade_post' => $tradepost
            ])
            ->getSingleScalarResult();
    }

    public function getLicenceGoodIdByTradepost(int $tradepost): int
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tlc.goods_id FROM %s tlc WHERE tlc.posts_id = :trade_post ORDER BY tlc.id DESC',
                    TradeLicenceCreation::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'trade_post' => $tradepost
            ])
            ->getSingleScalarResult();
    }

    public function getLicenceGoodAmountByTradepost(int $tradepost): int
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tlc.amount FROM %s tlc WHERE tlc.posts_id = :trade_post ORDER BY tlc.id DESC',
                    TradeLicenceCreation::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'trade_post' => $tradepost
            ])
            ->getSingleScalarResult();
    }
}