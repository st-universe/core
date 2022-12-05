<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Trade\TradeEnum;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePostInterface;

final class DealsRepository extends EntityRepository implements DealsRepositoryInterface
{

    public function prototype(): DealsInterface
    {
        return new Deals();
    }

    public function save(DealsInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(DealsInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getDeals(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT d FROM %s d
                WHERE :time < d.end
                ORDER BY d.id ASC',
                Deals::class,
            )
        )->setParameters([
            'time' => time()
        ])->getResult();
    }

    public function getFergLicense(int $userId): bool
    {
        $time = time();
        $result = $this->getEntityManager()->createQuery(sprintf('SELECT COUNT(tl.id) FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime AND tl.posts_id =:tradepostId', TradeLicense::class))
            ->setParameters([
                'userId' => $userId,
                'actime' => $time,
                'tradepostId' => TradeEnum::DEALS_FERG_TRADEPOST_ID
            ])->getSingleScalarResult();

        return $result > 0;
    }

    public function getFergTradePost(
        int $tradePostId
    ): ?TradePostInterface {
        return $this->findOneBy([
            'id' => $tradePostId
        ]);
    }

    public function getActiveDeals(int $userId): array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return [];
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.auction = FALSE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }

    public function getActiveDealsGoods(int $userId): ?array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return null;
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.auction = FALSE AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }

    public function getActiveDealsShips(int $userId): array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return null;
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.auction = FALSE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }

    public function getActiveDealsBuildplans(int $userId): array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return null;
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.auction = FALSE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }

    public function getActiveDealsGoodsPrestige(int $userId): array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return null;
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.auction = FALSE AND d.want_commodity IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }

    public function getActiveDealsShipsPrestige(int $userId): array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return null;
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.auction = FALSE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }

    public function getActiveDealsBuildplansPrestige(int $userId): array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return null;
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.auction = FALSE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }

    public function getActiveAuctions(int $userId): array
    {
        if ($this->getFergLicense($userId) == FALSE) {
            return null;
        } else {
            $time = time();
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT d FROM %s d WHERE d.end > :actime AND d.acution = TRUE
                    ',
                        Deals::class,
                    )
                )
                ->setParameters([
                    'actime' => $time,
                ])
                ->getResult();
        }
    }
}