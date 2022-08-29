<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\TradePostInterface;

final class TradePostRepository extends EntityRepository implements TradePostRepositoryInterface
{
    public function prototype(): TradePostInterface
    {
        return new TradePost();
    }

    public function save(TradePostInterface $setTradePost): void
    {
        $em = $this->getEntityManager();

        $em->persist($setTradePost);
    }

    public function delete(TradePostInterface $setTradePost): void
    {
        $em = $this->getEntityManager();

        $em->remove($setTradePost);
        $em->flush();
    }


    public function getByUserLicense(int $userId): array
    {
        $time = time();
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime ORDER BY tp.id ASC
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
}