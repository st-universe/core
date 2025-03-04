<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Repository\CrewSkillRepository;

#[Table(name: 'stu_crew_skill')]
#[Entity(repositoryClass: CrewSkillRepository::class)]
class CrewSkill implements CrewSkillInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $crew_id;

    #[Id]
    #[Column(type: 'smallint', enumType: CrewPositionEnum::class)]
    private CrewPositionEnum $position;

    #[Column(type: 'integer')]
    private int $expertise = 0;

    #[ManyToOne(targetEntity: 'Crew')]
    #[JoinColumn(name: 'crew_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CrewInterface $crew;

    #[Override]
    public function getPosition(): CrewPositionEnum
    {
        return $this->position;
    }

    #[Override]
    public function setPosition(CrewPositionEnum $position): CrewSkillInterface
    {
        $this->position = $position;

        return $this;
    }

    #[Override]
    public function getCrew(): CrewInterface
    {
        return $this->crew;
    }

    #[Override]
    public function setCrew(CrewInterface $crew): CrewSkillInterface
    {
        $this->crew = $crew;
        $this->crew_id = $crew->getId();

        return $this;
    }

    #[Override]
    public function increaseExpertise(int $amount): void
    {
        $this->expertise += $amount;
    }

    #[Override]
    public function getExpertise(): int
    {
        return $this->expertise;
    }

    #[Override]
    public function getRank(): CrewSkillLevelEnum
    {
        return CrewSkillLevelEnum::getForExpertise($this->expertise);
    }
}
