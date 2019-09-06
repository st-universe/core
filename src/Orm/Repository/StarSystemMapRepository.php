<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;

final class StarSystemMapRepository extends EntityRepository implements StarSystemMapRepositoryInterface
{
    public function getBySystemOrdered(int $starSystemId): array
    {
        return $this->findBy(
            ['systems_id' => $starSystemId],
            ['cy' => 'asc', 'cx' => 'asc']
        );
    }

    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMapInterface {
        return $this->findOneBy([
            'systems_id' => $starSystemId,
            'sx' => $sx,
            'sy' => $sy
        ]);
    }

    public function getByCoordinateRange(
        int $starSystemId,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m WHERE m.systems_id = :starSystemId AND
                        m.sx BETWEEN :startSx AND :endSx AND
                        m.sy BETWEEN :startSy AND :endSy',
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'starSystemId' => $starSystemId,
                'startSx' => $startSx,
                'endSx' => $endSx,
                'startSy' => $startSy,
                'endSy' => $endSy
            ])
            ->getResult();
    }
}