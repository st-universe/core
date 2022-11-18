<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\TradePostInterface;

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
        $em->flush();
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
        $time = time();
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.user_id < 100 AND tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime
                    )',
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

    public function getTradePostIdByShip(int $ship_id): int
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf('SELECT tp.id FROM $s tp WHERE tp.ship_id = :shipid', TradePost::class)
            )
            ->setParameters([
                'shipid' => $ship_id,
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
                    JOIN %s m 
                    WITH tp.map_id = m.id
                    WHERE tp.user_id < :firstUserId
                    ORDER BY abs(m.cx - :cx) + abs(m.cy - :cy) ASC',
                    TradePost::class,
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
}
