<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserLayerInterface;

/**
 * @extends EntityRepository<UserLayer>
 */
final class UserLayerRepository extends EntityRepository implements UserLayerRepositoryInterface
{
    #[Override]
    public function prototype(): UserLayerInterface
    {
        return new UserLayer();
    }

    #[Override]
    public function save(UserLayerInterface $userLayer): void
    {
        $em = $this->getEntityManager();

        $em->persist($userLayer);
    }

    #[Override]
    public function delete(UserLayerInterface $userLayer): void
    {
        $em = $this->getEntityManager();

        $em->remove($userLayer);
    }

    #[Override]
    public function getByMappingType(int $mappingType): array
    {
        return $this->findBy([
            'map_type' => $mappingType
        ]);
    }

    #[Override]
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
