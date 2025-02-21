<?php

namespace Stu\Orm\Entity;

use Stu\Component\Crew\CrewPositionEnum;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;

interface SkillEnhancementInterface
{
    public function getExpertise(): int;

    public function getType(): SkillEnhancementEnum;

    public function getPosition(): CrewPositionEnum;

    public function getDescription(): string;
}
