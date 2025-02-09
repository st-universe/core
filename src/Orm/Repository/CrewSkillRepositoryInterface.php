<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewSkill;
use Stu\Orm\Entity\CrewSkillInterface;

/**
 * @extends ObjectRepository<CrewSkill>
 *
 * @method null|CrewSkillInterface find(integer $id)
 */
interface CrewSkillRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewSkillInterface;

    public function save(CrewSkillInterface $post): void;

    public function delete(CrewSkillInterface $post): void;
}
