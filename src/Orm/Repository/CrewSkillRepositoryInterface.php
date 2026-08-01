<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewSkill;

/**
 * @extends ObjectRepository<CrewSkill>
 */
interface CrewSkillRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewSkill;

    public function save(CrewSkill $crewSkill): void;

    public function delete(CrewSkill $crewSkill): void;
}
