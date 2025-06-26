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
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Repository\ModuleQueueRepository;

#[Table(name: 'stu_modules_queue')]
#[Index(name: 'module_queue_colony_module_idx', columns: ['colony_id', 'module_id'])]
#[Entity(repositoryClass: ModuleQueueRepository::class)]
class ModuleQueue
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $colony_id = 0;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'integer')]
    private int $count = 0;

    #[Column(type: 'integer', enumType: BuildingFunctionEnum::class)]
    private BuildingFunctionEnum $buildingfunction = BuildingFunctionEnum::BASE_CAMP;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Colony $colony;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): ModuleQueue
    {
        $this->colony = $colony;
        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): ModuleQueue
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): ModuleQueue
    {
        $this->count = $amount;

        return $this;
    }

    public function getBuildingFunction(): BuildingFunctionEnum
    {
        return $this->buildingfunction;
    }

    public function setBuildingFunction(BuildingFunctionEnum $buildingFunction): ModuleQueue
    {
        $this->buildingfunction = $buildingFunction;

        return $this;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): ModuleQueue
    {
        $this->module = $module;

        return $this;
    }
}
