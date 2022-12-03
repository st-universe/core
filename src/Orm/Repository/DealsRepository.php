<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Trade\TradeEnum;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePost;

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
        $result = $this->getEntityManager()->createQuery(sprintf('SELECT COUNT(tl.id) FROM %s tl WHERE tl.userId = :userId AND tl.exoured > :actime AND tl.posts_id =:tradepostId', TradeLicense::class))
            ->setParameters([
                'userId' => $userId,
                'actime' => $time,
                'tradepostId' => TradeEnum::DEALS_FERG_TRADEPOST_ID
            ])->getSingleScalarResult();

        return $result > 0;
    }

    public function getActivDeals(int $userId): array
    {
        //        if ($this->getFergLicense($userId) == FALSE) {
        //            return;
        //        } else {
        $time = time();
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d WHERE d.end > :actime
                    )',
                    Deals::class,
                )
            )
            ->setParameters([
                'actime' => $time,
            ])
            ->getResult();
        //        }
    }
}