<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<TradePost>
 */
final class TradePostRepository extends EntityRepository implements TradePostRepositoryInterface
{
    public function prototype(): TradePostInterface
    {
        return new TradePost();
    }

    public function save(TradePostInterface $tradePost): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradePost);
    }

    public function delete(TradePostInterface $tradePost): void
    {
        $em = $this->getEntityManager();

        $em->remove($tradePost);
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId]
        );
    }

    public function getByUserLicense(int $userId): array
    {
        $time = time();
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime
                    ) ORDER BY tp.id ASC',
                    TradePost::class,
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => $time
            ])
            ->getResult();
    }

    public function getByUserLicenseOnlyNPC(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.user_id < :firstUserId AND tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime
                    )',
                    TradePost::class,
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => time(),
                'firstUserId' => UserEnum::USER_FIRST_ID
            ])
            ->getResult();
    }

    public function getByUserLicenseOnlyFerg(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.user_id = 14 AND tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime AND tl.posts_id = :tradepostId
                    )',
                    TradePost::class,
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => time(),
                'tradepostId' => TradeEnum::DEALS_FERG_TRADEPOST_ID
            ])
            ->getResult();
    }

    public function getClosestNpcTradePost(int $cx, int $cy): TradePostInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp
                    FROM %s tp
                    JOIN %s s
                    WITH tp.ship_id = s.id
                    JOIN %s m
                    WITH s.map_id = m.id
                    WHERE tp.user_id < :firstUserId
                    ORDER BY abs(m.cx - :cx) + abs(m.cy - :cy) ASC',
                    TradePost::class,
                    Ship::class,
                    Map::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'cx' => $cx,
                'cy' => $cy,
                'firstUserId' => UserEnum::USER_FIRST_ID
            ])
            ->getSingleResult();
    }

    public function getFergTradePost(
        int $tradePostId
    ): ?TradePostInterface {
        return $this->findOneBy([
            'id' => $tradePostId
        ]);
    }

    public function getUsersWithStorageOnTradepost(int $tradePostId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT u FROM %s u
                    WHERE EXISTS(
                            SELECT s.id FROM %s s
                            WHERE s.user_id = u.id
                            AND (s.tradepost_id = :tradePostId
                                OR s.tradeoffer_id IN (SELECT o.id FROM %s o WHERE o.posts_id = :tradePostId))
                            )',
                    User::class,
                    Storage::class,
                    TradeOffer::class
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId
            ])
            ->getResult();
    }
}
