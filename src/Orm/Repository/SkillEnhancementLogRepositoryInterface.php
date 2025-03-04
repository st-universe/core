<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\SkillEnhancementLog;
use Stu\Orm\Entity\SkillEnhancementLogInterface;

/**
 * @extends ObjectRepository<SkillEnhancementLog>
 *
 * @method array<SkillEnhancementLogInterface> findAll()
 */
interface SkillEnhancementLogRepositoryInterface extends ObjectRepository
{
    public function prototype(): SkillEnhancementLogInterface;

    public function save(SkillEnhancementLogInterface $log): void;

    /** @return array<SkillEnhancementLogInterface> */
    public function getForCrewman(CrewInterface $crew): array;
}
