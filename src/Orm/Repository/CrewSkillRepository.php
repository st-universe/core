<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\CrewSkill;
use Stu\Orm\Entity\CrewSkillInterface;

/**
 * @extends EntityRepository<CrewSkill>
 */
final class CrewSkillRepository extends EntityRepository implements CrewSkillRepositoryInterface
{

    #[Override]
    public function prototype(): CrewSkillInterface
    {
        return new CrewSkill();
    }

    #[Override]
    public function save(CrewSkillInterface $crewSkill): void
    {
        $em = $this->getEntityManager();

        $em->persist($crewSkill);
    }

    #[Override]
    public function delete(CrewSkillInterface $crewSkill): void
    {
        $em = $this->getEntityManager();

        $em->remove($crewSkill);
    }
}
