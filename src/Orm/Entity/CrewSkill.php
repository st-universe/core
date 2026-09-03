<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Repository\CrewSkillRepository;

#[Table(name: 'stu_crew_skill')]
#[Entity(repositoryClass: CrewSkillRepository::class)]
class CrewSkill
{
    #[Id]
    #[ManyToOne(targetEntity: Crew::class, inversedBy: 'skills')]
    #[JoinColumn(name: 'crew_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Crew $crew;

    #[Id]
    #[Column(type: 'smallint', enumType: CrewTypeEnum::class)]
    private CrewTypeEnum $position;

    #[Column(type: 'integer')]
    private int $expertise = 0;

    public function getPosition(): CrewTypeEnum
    {
        return $this->position;
    }

    public function setPosition(CrewTypeEnum $position): CrewSkill
    {
        $this->position = $position;

        return $this;
    }

    public function getCrew(): Crew
    {
        return $this->crew;
    }

    public function setCrew(Crew $crew): CrewSkill
    {
        $this->crew = $crew;

        return $this;
    }

    public function increaseExpertise(int $amount): void
    {
        $this->expertise = max(0, $this->expertise + $amount);
    }

    public function getExpertise(): int
    {
        return $this->expertise;
    }

    public function getRank(): CrewSkillLevelEnum
    {
        return CrewSkillLevelEnum::getForExpertise($this->expertise);
    }
}
