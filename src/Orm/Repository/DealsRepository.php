<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\DealsInterface;

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
            'userId' => $userId,
            'time' => time()
        ])->getResult();
    }
}