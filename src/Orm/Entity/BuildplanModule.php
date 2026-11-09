<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use InvalidArgumentException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Repository\BuildplanModuleRepository;

#[Table(name: 'stu_buildplans_modules')]
#[UniqueConstraint(name: 'buildplan_module_type_idx', columns: ['buildplan_id', 'module_type', 'module_special'])]
#[Entity(repositoryClass: BuildplanModuleRepository::class)]
class BuildplanModule
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $buildplan_id = 0;

    #[Column(type: 'smallint', enumType: SpacecraftModuleTypeEnum::class)]
    private SpacecraftModuleTypeEnum $module_type = SpacecraftModuleTypeEnum::HULL;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $module_special = null;

    #[Column(type: 'smallint')]
    private int $module_count = 1;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class, inversedBy: 'modules')]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SpacecraftBuildplan $buildplan;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Module $module;

    #[Column(name: 'improvement_steps', type: 'smallint', options: ['default' => 0])]
    private int $improvementSteps = 0;

    public function getImprovementSteps(): int
    {
        return $this->improvementSteps;
    }

    public function setImprovementSteps(int $steps): self
    {
        if ($steps < 0 || $steps > 70) {
            throw new InvalidArgumentException('Module improvement must be between 0 and 70');
        }
        $this->improvementSteps = $steps;

        return $this;
    }

    public function getQualityLabel(): string
    {
        return $this->improvementSteps === 0 ? 'Standard' : '+' . number_format($this->improvementSteps / 10, 1, ',', '') . '%';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildplan(): SpacecraftBuildplan
    {
        return $this->buildplan;
    }

    public function setBuildplan(SpacecraftBuildplan $buildplan): BuildplanModule
    {
        $this->buildplan = $buildplan;

        return $this;
    }

    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return $this->module_type;
    }

    public function setModuleType(SpacecraftModuleTypeEnum $moduleType): BuildplanModule
    {
        $this->module_type = $moduleType;

        return $this;
    }

    public function getModuleCount(): int
    {
        return $this->module_count;
    }

    public function setModuleCount(int $moduleCount): BuildplanModule
    {
        $this->module_count = $moduleCount;

        return $this;
    }

    public function getModuleSpecial(): ?int
    {
        return $this->module_special;
    }

    public function setModuleSpecial(?int $moduleSpecial): BuildplanModule
    {
        $this->module_special = $moduleSpecial;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): BuildplanModule
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): BuildplanModule
    {
        $this->module = $module;

        return $this;
    }
}
