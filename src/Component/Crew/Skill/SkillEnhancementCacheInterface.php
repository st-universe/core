<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Orm\Entity\SkillEnhancementInterface;

interface SkillEnhancementCacheInterface
{
    /** @return null|array<int, SkillEnhancementInterface> */
    public function getSkillEnhancements(SkillEnhancementEnum $type): ?array;
}
