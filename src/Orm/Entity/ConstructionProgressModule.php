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
use Stu\Orm\Repository\ConstructionProgressModuleRepository;

#[Table(name: 'stu_progress_module')]
#[Entity(repositoryClass: ConstructionProgressModuleRepository::class)]
class ConstructionProgressModule
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $progress_id = 0;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[ManyToOne(targetEntity: ConstructionProgress::class)]
    #[JoinColumn(name: 'progress_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ConstructionProgress $progress;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getConstructionProgress(): ConstructionProgress
    {
        return $this->progress;
    }

    public function setConstructionProgress(ConstructionProgress $progress): ConstructionProgressModule
    {
        $this->progress = $progress;
        return $this;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): ConstructionProgressModule
    {
        $this->module = $module;

        return $this;
    }
}
