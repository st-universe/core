<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\CrewSkill;

/**
 * @extends EntityRepository<CrewSkill>
 */
final class CrewSkillRepository extends EntityRepository implements CrewSkillRepositoryInterface
{
    #[\Override]
    public function prototype(): CrewSkill
    {
        return new CrewSkill();
    }

    #[\Override]
    public function save(CrewSkill $crewSkill): void
    {
        $this->getEntityManager()->persist($crewSkill);
    }

    #[\Override]
    public function delete(CrewSkill $crewSkill): void
    {
        $this->getEntityManager()->remove($crewSkill);
    }
}
