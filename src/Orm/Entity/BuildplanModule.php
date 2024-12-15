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
use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Repository\BuildplanModuleRepository;

#[Table(name: 'stu_buildplans_modules')]
#[UniqueConstraint(name: 'buildplan_module_type_idx', columns: ['buildplan_id', 'module_type', 'module_special'])]
#[Entity(repositoryClass: BuildplanModuleRepository::class)]
class BuildplanModule implements BuildplanModuleInterface
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

    #[Column(type: 'smallint', nullable: true)]
    private ?int $module_special = null;

    #[Column(type: 'smallint')]
    private int $module_count = 1;

    #[ManyToOne(targetEntity: 'SpacecraftBuildplan')]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplanInterface $buildplan;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ModuleInterface $module;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getBuildplan(): SpacecraftBuildplanInterface
    {
        return $this->buildplan;
    }

    #[Override]
    public function setBuildplan(SpacecraftBuildplanInterface $buildplan): BuildplanModuleInterface
    {
        $this->buildplan = $buildplan;

        return $this;
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return $this->module_type;
    }

    #[Override]
    public function setModuleType(SpacecraftModuleTypeEnum $moduleType): BuildplanModuleInterface
    {
        $this->module_type = $moduleType;

        return $this;
    }

    #[Override]
    public function getModuleCount(): int
    {
        return $this->module_count;
    }

    #[Override]
    public function setModuleCount(int $moduleCount): BuildplanModuleInterface
    {
        $this->module_count = $moduleCount;

        return $this;
    }

    #[Override]
    public function getModuleSpecial(): ?int
    {
        return $this->module_special;
    }

    #[Override]
    public function setModuleSpecial(?int $moduleSpecial): BuildplanModuleInterface
    {
        $this->module_special = $moduleSpecial;

        return $this;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): BuildplanModuleInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    #[Override]
    public function setModule(ModuleInterface $module): BuildplanModuleInterface
    {
        $this->module = $module;

        return $this;
    }
}
