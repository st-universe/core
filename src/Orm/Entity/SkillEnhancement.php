<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Orm\Repository\SkillEnhancementRepository;

#[Table(name: 'stu_skill_enhancement')]
#[UniqueConstraint(name: 'skill_enhancement_unique_idx', columns: ['type', 'position'])]
#[Entity(repositoryClass: SkillEnhancementRepository::class)]
class SkillEnhancement
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint', enumType: SkillEnhancementEnum::class)]
    private SkillEnhancementEnum $type;

    #[Column(type: 'smallint', enumType: CrewTypeEnum::class)]
    private CrewTypeEnum $position;

    #[Column(type: 'integer')]
    private int $expertise;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cooldown = null;

    #[Column(type: 'string')]
    private string $description;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): SkillEnhancementEnum
    {
        return $this->type;
    }

    public function setType(SkillEnhancementEnum $type): SkillEnhancement
    {
        $this->type = $type;

        return $this;
    }

    public function getPosition(): CrewTypeEnum
    {
        return $this->position;
    }

    public function setPosition(CrewTypeEnum $position): SkillEnhancement
    {
        $this->position = $position;

        return $this;
    }

    public function getExpertise(): int
    {
        return $this->expertise;
    }

    public function setExpertise(int $expertise): SkillEnhancement
    {
        $this->expertise = $expertise;

        return $this;
    }

    public function getCooldown(): ?int
    {
        return $this->cooldown;
    }

    public function setCooldown(?int $cooldown): SkillEnhancement
    {
        $this->cooldown = $cooldown;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): SkillEnhancement
    {
        $this->description = $description;

        return $this;
    }
}
