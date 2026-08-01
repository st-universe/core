<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Orm\Entity\SkillEnhancement;

interface SkillEnhancementCacheInterface
{
    /** @return null|array<int, SkillEnhancement> */
    public function getSkillEnhancements(SkillEnhancementEnum $type): ?array;
}
