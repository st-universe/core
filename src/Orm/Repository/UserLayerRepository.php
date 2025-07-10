<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserLayer;

/**
 * @extends EntityRepository<UserLayer>
 */
final class UserLayerRepository extends EntityRepository implements UserLayerRepositoryInterface
{
    #[Override]
    public function prototype(): UserLayer
    {
        return new UserLayer();
    }

    #[Override]
    public function save(UserLayer $userLayer): void
    {
        $em = $this->getEntityManager();

        $em->persist($userLayer);
    }

    #[Override]
    public function delete(UserLayer $userLayer): void
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
}
