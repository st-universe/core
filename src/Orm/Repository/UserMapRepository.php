<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserMap;

final class UserMapRepository extends EntityRepository implements UserMapRepositoryInterface
{

    public function insertMapFieldsForUser(int $userId, int $cx, int $cy, int $range): void
    {
        $this->getEntityManager()->getConnection()->query(
            sprintf(
                'INSERT INTO stu_user_map (id,user_id,cx,cy,map_id)
                (SELECT uuid_in(overlay(overlay(md5(random()::text || \':\' || clock_timestamp()::text) placing \'4\' from 13) placing to_hex(floor(random()*(11-8+1) + 8)::int)::text from 17)::cstring),%d as user_id,cx,cy,id as map_id FROM stu_map WHERE cx BETWEEN %d AND %d AND cy BETWEEN %d AND %d)
                ON CONFLICT DO NOTHING',
                $userId,
                $cx - $range,
                $cx + $range,
                $cy - $range,
                $cy + $range
            )
        );
    }

    public function deleteMapFieldsForUser(int $userId, int $cx, int $cy, int $range): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s um WHERE um.user_id = :userId AND um.cx BETWEEN :startCx AND :endCx AND um.cy
                    BETWEEN :startCy AND :endCy',
                UserMap::class
            )
        )->setParameters([
            'userId' => $userId,
            'startCx' => $cx - $range,
            'endCx' => $cx + $range,
            'startCy' => $cy - $range,
            'endCy' => $cy + $range,
        ])->execute();
    }

    public function getAmountByUser(int $userId): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(um) FROM %s um WHERE um.user_id = :userId',
                UserMap::class
            )
        )->setParameters([
            'userId' => $userId
        ])->getSingleScalarResult();
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s um WHERE um.user_id = :userId',
                UserMap::class
            )
        )->setParameters([
            'userId' => $userId
        ])->execute();
    }
}
