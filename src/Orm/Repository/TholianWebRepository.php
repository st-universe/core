<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\TholianWebInterface;

/**
 * @extends EntityRepository<TholianWeb>
 */
final class TholianWebRepository extends EntityRepository implements TholianWebRepositoryInterface
{
    #[Override]
    public function save(TholianWebInterface $web): void
    {
        $em = $this->getEntityManager();

        $em->persist($web);
    }

    #[Override]
    public function delete(TholianWebInterface $web): void
    {
        $em = $this->getEntityManager();

        $em->remove($web);
    }

    #[Override]
    public function getWebAtLocation(ShipInterface $ship): ?TholianWebInterface
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
