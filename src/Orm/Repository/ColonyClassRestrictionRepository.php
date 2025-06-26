<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyClassRestriction;
use Stu\Orm\Entity\Terraforming;
use Stu\Orm\Entity\Building;

/**
 * @extends EntityRepository<ColonyClassRestriction>
 */
final class ColonyClassRestrictionRepository extends EntityRepository implements ColonyClassRestrictionRepositoryInterface
{
    #[Override]
    public function prototype(): ColonyClassRestriction
    {
        return new ColonyClassRestriction();
    }

    #[Override]
    public function save(ColonyClassRestriction $restriction): void
    {
        $em = $this->getEntityManager();
        $em->persist($restriction);
    }

    #[Override]
    public function delete(ColonyClassRestriction $restriction): void
    {
        $em = $this->getEntityManager();
        $em->remove($restriction);
    }
}
