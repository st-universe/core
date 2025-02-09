<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Override;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Orm\Repository\SkillEnhancementRepository;

#[Table(name: 'stu_skill_enhancement')]
#[Entity(repositoryClass: SkillEnhancementRepository::class)]
#[UniqueConstraint(name: 'skill_enhancement_unique_idx', columns: ['type', 'position'])]
class SkillEnhancement implements SkillEnhancementInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint', enumType: SkillEnhancementEnum::class)]
    private SkillEnhancementEnum $type;

    #[Column(type: 'smallint', enumType: CrewPositionEnum::class)]
    private CrewPositionEnum $position;

    #[Column(type: 'integer')]
    private int $expertise;

    #[Column(type: 'string')]
    private string $description;

    #[Override]
    public function getType(): SkillEnhancementEnum
    {
        return $this->type;
    }

    #[Override]
    public function getPosition(): CrewPositionEnum
    {
        return $this->position;
    }

    #[Override]
    public function getExpertise(): int
    {
        return $this->expertise;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
