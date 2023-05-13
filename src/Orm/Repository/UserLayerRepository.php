<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserLayerInterface;

/**
 * @extends EntityRepository<UserLayer>
 */
final class UserLayerRepository extends EntityRepository implements UserLayerRepositoryInterface
{
    public function prototype(): UserLayerInterface
    {
        return new UserLayer();
    }

    public function save(UserLayerInterface $userLayer): void
    {
        $em = $this->getEntityManager();

        $em->persist($userLayer);
    }

    public function delete(UserLayerInterface $userLayer): void
    {
        $em = $this->getEntityManager();

        $em->remove($userLayer);
    }

    public function getByMappingType(int $mappingType): array
    {
        return $this->findBy([
            'map_type' => $mappingType
        ]);
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ul
                WHERE ul.user_id = :userId',
                UserLayer::class
            )
        )->setParameters([
            'userId' => $userId
        ])->execute();
    }

    public function truncateAllUserLayer(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ul',
                UserLayer::class
            )
        )->execute();
    }
}
