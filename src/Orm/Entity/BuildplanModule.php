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

#[Table(name: 'stu_buildplans_modules')]
#[UniqueConstraint(name: 'buildplan_module_type_idx', columns: ['buildplan_id', 'module_type', 'module_special'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\BuildplanModuleRepository')]
class BuildplanModule implements BuildplanModuleInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $buildplan_id = 0;

    #[Column(type: 'smallint')]
    private int $module_type = 0;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $module_special = null;

    #[Column(type: 'smallint')]
    private int $module_count = 1;

    #[ManyToOne(targetEntity: 'ShipBuildplan')]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipBuildplanInterface $buildplan;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ModuleInterface $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildplan(): ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    public function setBuildplan(ShipBuildplanInterface $buildplan): BuildplanModuleInterface
    {
        $this->buildplan = $buildplan;

        return $this;
    }

    public function getModuleType(): int
    {
        return $this->module_type;
    }

    public function setModuleType(int $moduleType): BuildplanModuleInterface
    {
        $this->module_type = $moduleType;

        return $this;
    }

    public function getModuleCount(): int
    {
        return $this->module_count;
    }

    public function setModuleCount(int $moduleCount): BuildplanModuleInterface
    {
        $this->module_count = $moduleCount;

        return $this;
    }

    public function getModuleSpecial(): ?int
    {
        return $this->module_special;
    }

    public function setModuleSpecial(?int $moduleSpecial): BuildplanModuleInterface
    {
        $this->module_special = $moduleSpecial;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): BuildplanModuleInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function setModule(ModuleInterface $module): BuildplanModuleInterface
    {
        $this->module = $module;

        return $this;
    }
}
