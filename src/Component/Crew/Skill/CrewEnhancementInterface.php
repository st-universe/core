<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface CrewEnhancementInterface
{
    public function addExpertise(
        SpacecraftInterface|SpacecraftWrapperInterface $target,
        SkillEnhancementEnum $trigger,
        int $percentage
    ): void;
}
