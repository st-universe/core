<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\ModuleBuildingFunctionRepository;
use Override;
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
#[Entity(repositoryClass: ModuleBuildingFunctionRepository::class)]
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): ModuleBuildingFunctionInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getBuildingFunction(): int
    {
        return $this->buildingfunction;
    }

    #[Override]
    public function setBuildingFunction(int $buildingFunction): ModuleBuildingFunctionInterface
    {
        $this->buildingfunction = $buildingFunction;

        return $this;
    }

    #[Override]
    public function getModule(): ModuleInterface
    {
        return $this->module;
    }
}
