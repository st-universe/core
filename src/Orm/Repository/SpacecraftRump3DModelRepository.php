<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRump3DModel;

/**
 * @extends EntityRepository<SpacecraftRump3DModel>
 */
final class SpacecraftRump3DModelRepository extends EntityRepository implements SpacecraftRump3DModelRepositoryInterface
{
    #[\Override]
    public function save(SpacecraftRump3DModel $entity): void
    {
        $em = $this->getEntityManager();

        $em->persist($entity);
    }

    #[\Override]
    public function getBySpacecraftRump(SpacecraftRump $rump): ?SpacecraftRump3DModel
    {
        return $this->findOneBy([
            'rump' => $rump
        ]);
    }
}
