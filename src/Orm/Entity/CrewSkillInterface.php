<?php

namespace Stu\Orm\Entity;

use Stu\Component\Crew\CrewPositionEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;

interface CrewSkillInterface
{
    public function getPosition(): CrewPositionEnum;

    public function setPosition(CrewPositionEnum $position): CrewSkillInterface;

    public function getCrew(): CrewInterface;

    public function setCrew(CrewInterface $crew): CrewSkillInterface;

    public function increaseExpertise(int $amount): void;

    public function getExpertise(): int;

    public function getRank(): CrewSkillLevelEnum;
}
