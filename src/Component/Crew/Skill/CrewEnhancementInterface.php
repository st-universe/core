<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

interface CrewEnhancementInterface
{
    public function addExpertise(
        Spacecraft|SpacecraftWrapperInterface $target,
        SkillEnhancementEnum $trigger,
        int $percentage = 100
    ): void;
}
