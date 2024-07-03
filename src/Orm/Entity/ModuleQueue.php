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
use Override;
use Stu\Orm\Repository\ModuleQueueRepository;

#[Table(name: 'stu_modules_queue')]
#[Index(name: 'module_queue_colony_module_idx', columns: ['colony_id', 'module_id'])]
#[Entity(repositoryClass: ModuleQueueRepository::class)]
class ModuleQueue implements ModuleQueueInterface
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

    #[Column(type: 'integer')]
    private int $buildingfunction = 0;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ModuleInterface $module;

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyInterface $colony;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(ColonyInterface $colony): ModuleQueueInterface
    {
        $this->colony = $colony;
        return $this;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): ModuleQueueInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->count;
    }

    #[Override]
    public function setAmount(int $amount): ModuleQueueInterface
    {
        $this->count = $amount;

        return $this;
    }

    #[Override]
    public function getBuildingFunction(): int
    {
        return $this->buildingfunction;
    }

    #[Override]
    public function setBuildingFunction(int $buildingFunction): ModuleQueueInterface
    {
        $this->buildingfunction = $buildingFunction;

        return $this;
    }

    #[Override]
    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    #[Override]
    public function setModule(ModuleInterface $module): ModuleQueueInterface
    {
        $this->module = $module;

        return $this;
    }
}
