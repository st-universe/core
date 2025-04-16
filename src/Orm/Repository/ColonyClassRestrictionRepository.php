<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\ColonyClassRestriction;
use Stu\Orm\Entity\ColonyClassRestrictionInterface;
use Stu\Orm\Entity\TerraformingInterface;
use Stu\Orm\Entity\BuildingInterface;

/**
 * @extends EntityRepository<ColonyClassRestriction>
 */
final class ColonyClassRestrictionRepository extends EntityRepository implements ColonyClassRestrictionRepositoryInterface
{
    #[Override]
    public function prototype(): ColonyClassRestrictionInterface
    {
        return new ColonyClassRestriction();
    }

    #[Override]
    public function save(ColonyClassRestrictionInterface $restriction): void
    {
        $em = $this->getEntityManager();
        $em->persist($restriction);
    }

    #[Override]
    public function delete(ColonyClassRestrictionInterface $restriction): void
    {
        $em = $this->getEntityManager();
        $em->remove($restriction);
    }
}