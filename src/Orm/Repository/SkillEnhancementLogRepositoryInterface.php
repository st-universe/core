<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Entity\SkillEnhancementLog;

/**
 * @extends ObjectRepository<SkillEnhancementLog>
 */
interface SkillEnhancementLogRepositoryInterface extends ObjectRepository
{
    public function prototype(): SkillEnhancementLog;

    public function save(SkillEnhancementLog $log): void;

    /** @return array<SkillEnhancementLog> */
    public function getForCrewman(Crew $crew): array;

    public function hasCrewExperienceSince(Crew $crew, SkillEnhancement $enhancement, int $timestamp): bool;
}
