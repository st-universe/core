<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Map\MapEnum;
use Stu\Module\Starmap\Lib\ExploreableStarMap;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapInterface;

final class MapRepository extends EntityRepository implements MapRepositoryInterface
{
    public function getAllOrdered(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m WHERE m.cx BETWEEN 1 AND :mapMaxX AND m.cy BETWEEN 1 AND :mapMaxY
                        ORDER BY m.cy, m.cx',
                    Map::class
                )
            )
            ->setParameters([
                'mapMaxX' => MapEnum::MAP_MAX_X,
                'mapMaxY' => MapEnum::MAP_MAX_Y
            ])
            ->getResult();
    }

    public function getByCoordinates(int $cx, int $cy): ?MapInterface
    {
        return $this->findOneBy([
            'cx' => $cx,
            'cy' => $cy
        ]);
    }

    public function getByCoordinateRange(
        int $startCx,
        int $endCx,
        int $startCy,
        int $endCy
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m WHERE m.cx BETWEEN :startCx AND :endCx AND m.cy BETWEEN :startCy AND :endCy',
                    Map::class
                )
            )
            ->setParameters([
                'startCx' => $startCx,
                'endCx' => $endCx,
                'startCy' => $startCy,
                'endCy' => $endCy
            ])
            ->getResult();
    }

    public function save(MapInterface $map): void
    {
        $em = $this->getEntityManager();

        $em->persist($map);
        $em->flush();
    }

    public function getExplored(int $userId, int $startX, int $endX, int $cy): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(ExploreableStarMap::class, 'm');
        $rsm->addFieldResult('m', 'id', 'id');
        $rsm->addFieldResult('m', 'cx', 'cx');
        $rsm->addFieldResult('m', 'cy', 'cy');
        $rsm->addFieldResult('m', 'field_id', 'field_id');
        $rsm->addFieldResult('m', 'bordertype_id', 'bordertype_id');
        $rsm->addFieldResult('m', 'user_id', 'user_id');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT m.id,m.cx,m.cy,m.field_id,m.systems_id,m.bordertype_id,um.user_id FROM stu_map
                    m LEFT JOIN stu_user_map um ON um.cy = m.cy AND um.cx = m.cx AND um.user_id = :userId WHERE m.cx
                    BETWEEN :startX AND :endX AND m.cy = :cy',
                $rsm
            )
            ->setParameters([
                'userId' => $userId,
                'startX' => $startX,
                'endX' => $endX,
                'cy' => $cy
            ])
            ->getResult();
    }
}
