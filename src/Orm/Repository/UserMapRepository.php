<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLayerInterface;
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
                'INSERT INTO stu_user_map (user_id,layer_id,cx,cy,map_id)
                (SELECT %d as user_id,layer_id,cx,cy,id as map_id
                FROM stu_map
                WHERE cx BETWEEN %d AND %d
                AND cy BETWEEN %d AND %d
                AND layer_id = %d)
                ON CONFLICT DO NOTHING',
                $userId,
                $cx - $range,
                $cx + $range,
                $cy - $range,
                $cy + $range,
                $layerId
            )
        );
    }

    #[Override]
    public function deleteMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s um
                WHERE um.user_id = :userId
                AND um.layer_id = :layerId
                AND um.cx BETWEEN :startCx AND :endCx
                AND um.cy BETWEEN :startCy AND :endCy',
                UserMap::class
            )
        )->setParameters([
            'userId' => $userId,
            'layerId' => $layerId,
            'startCx' => $cx - $range,
            'endCx' => $cx + $range,
            'startCy' => $cy - $range,
            'endCy' => $cy + $range,
        ])->execute();
    }

    #[Override]
    public function getAmountByUser(UserInterface $user, LayerInterface $layer): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(um)
                FROM %s um
                WHERE um.user_id = :userId
                AND um.layer_id = :layerId',
                UserMap::class
            )
        )->setParameters([
            'userId' => $user->getId(),
            'layerId' => $layer->getId()
        ])->getSingleScalarResult();
    }

    #[Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s um
                WHERE um.user_id = :userId',
                UserMap::class
            )
        )->setParameters([
            'userId' => $userId
        ])->execute();
    }

    #[Override]
    public function truncateByUserAndLayer(UserLayerInterface $userLayer): void
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

    #[Override]
    public function truncateAllUserMaps(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s um',
                UserMap::class
            )
        )->execute();
    }
}
