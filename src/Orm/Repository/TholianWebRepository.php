<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\TholianWeb;

/**
 * @extends EntityRepository<TholianWeb>
 */
final class TholianWebRepository extends EntityRepository implements TholianWebRepositoryInterface
{
    #[Override]
    public function save(TholianWeb $web): void
    {
        $em = $this->getEntityManager();

        $em->persist($web);
    }

    #[Override]
    public function delete(TholianWeb $web): void
    {
        $em = $this->getEntityManager();

        $em->remove($web);
    }

    #[Override]
    public function getWebAtLocation(Ship $ship): ?TholianWeb
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT tw FROM %s tw
                 WHERE tw.location = :location',
                TholianWeb::class
            )
        )->setParameters([
            'location' => $ship->getLocation()
        ])->getOneOrNullResult();
    }

    #[Override]
    public function getFinishedWebs(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tw FROM %s tw
                    WHERE tw.finished_time < :time',
                    TholianWeb::class
                )
            )
            ->setParameters([
                'time' => time()
            ])
            ->getResult();
    }
}
