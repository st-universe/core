<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill\Event;

use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Orm\Entity\Spacecraft;

final class CrewExperienceEvent
{
    public function __construct(
        private Spacecraft $spacecraft,
        private SkillEnhancementEnum $trigger,
        private int $percentage = 100
    ) {}

    public function getSpacecraft(): Spacecraft
    {
        return $this->spacecraft;
    }

    public function getTrigger(): SkillEnhancementEnum
    {
        return $this->trigger;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }
}
