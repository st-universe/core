<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_modules_buildingfunction')]
#[Index(name: 'module_buildingfunction_idx', columns: ['module_id', 'buildingfunction'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ModuleBuildingFunctionRepository')]
class ModuleBuildingFunction implements ModuleBuildingFunctionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'integer')]
    private int $buildingfunction = 0;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ModuleInterface $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): ModuleBuildingFunctionInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getBuildingFunction(): int
    {
        return $this->buildingfunction;
    }

    public function setBuildingFunction(int $buildingFunction): ModuleBuildingFunctionInterface
    {
        $this->buildingfunction = $buildingFunction;

        return $this;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }
}
