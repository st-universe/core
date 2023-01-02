<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\TholianWebInterface;

final class TholianWebRepository extends EntityRepository implements TholianWebRepositoryInterface
{
    public function prototype(): TholianWebInterface
    {
        return new TholianWeb();
    }

    public function save(TholianWebInterface $web): void
    {
        $em = $this->getEntityManager();

        $em->persist($web);
    }

    public function delete(TholianWebInterface $web): void
    {
        $em = $this->getEntityManager();

        $em->remove($web);
    }

    public function getWebAtLocation(ShipInterface $ship): ?TholianWebInterface
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT tw FROM %s tw
                 JOIN %s s
                 WITH tw.ship_id = s.id
                 WHERE s.map_id = :mapId
                 AND s.starsystem_map_id = :sysMapId',
                TholianWeb::class,
                Ship::class
            )
        )->setParameters([
            'mapId' => $ship->getMap() === null ? null : $ship->getMap()->getId(),
            'sysMapId' => $ship->getStarsystemMap() === null ? null : $ship->getStarsystemMap()->getId()
        ])->getOneOrNullResult();
    }

    public function getFinishedWebs(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT tw FROM %s tw
                WHERE tw.finished_time < :time',
                TholianWeb::class
            )
        )->setParameters([
            'time' => time()
        ])->getResult();
    }
}
