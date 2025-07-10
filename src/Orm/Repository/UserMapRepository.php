<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserMap;

/**
 * @extends EntityRepository<UserMap>
 */
final class UserMapRepository extends EntityRepository implements UserMapRepositoryInterface
{
    #[Override]
    public function insertMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            sprintf(
                'INSERT INTO stu_user_map (user_id, layer_id, cx, cy, map_id)
                SELECT %d as user_id, l.layer_id, l.cx, l.cy, m.id as map_id
                FROM stu_map m
                JOIN stu_location l
                ON m.id = l.id
                WHERE l.cx BETWEEN %d AND %d
                AND l.cy BETWEEN %d AND %d
                AND l.layer_id = %d
                AND NOT EXISTS (SELECT * FROM stu_user_map um
                                WHERE um.cx = l.cx
                                AND um.cy = l.cy
                                AND um.user_id = %d
                                AND um.layer_id = l.layer_id)',
                $userId,
                $cx - $range,
                $cx + $range,
                $cy - $range,
                $cy + $range,
                $layerId,
                $userId,
            )
        );
    }

    #[Override]
    public function getAmountByUser(User $user, Layer $layer): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(um)
                FROM %s um
                WHERE um.user = :user
                AND um.layer = :layer',
                UserMap::class
            )
        )->setParameters([
            'user' => $user,
            'layer' => $layer
        ])->getSingleScalarResult();
    }

    #[Override]
    public function truncateByUser(User $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s um
                WHERE um.user = :user',
                UserMap::class
            )
        )->setParameters([
            'user' => $user
        ])->execute();
    }

    #[Override]
    public function truncateByUserAndLayer(UserLayer $userLayer): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s um
                WHERE um.user = :user
                AND um.layer = :layer',
                UserMap::class
            )
        )->setParameters([
            'user' => $userLayer->getUser(),
            'layer' => $userLayer->getLayer()
        ])->execute();
    }
}
