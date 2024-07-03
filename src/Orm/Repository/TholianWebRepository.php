<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\TholianWebInterface;

/**
 * @extends EntityRepository<TholianWeb>
 */
final class TholianWebRepository extends EntityRepository implements TholianWebRepositoryInterface
{
    #[Override]
    public function prototype(): TholianWebInterface
    {
        return new TholianWeb();
    }

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
        $starSystemMap = $ship->getStarsystemMap();

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT tw FROM %s tw
                 JOIN %s s
                 WITH tw.ship_id = s.id
                 WHERE s.%s = :mapId',
                TholianWeb::class,
                Ship::class,
                $starSystemMap === null ? 'map_id' : 'starsystem_map_id',
            )
        )->setParameters([
            'mapId' => $ship->getCurrentMapField()->getId()
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
