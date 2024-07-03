<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_progress_module')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ConstructionProgressModuleRepository')]
class ConstructionProgressModule implements ConstructionProgressModuleInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $progress_id = 0;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[ManyToOne(targetEntity: 'ConstructionProgress')]
    #[JoinColumn(name: 'progress_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ConstructionProgressInterface $progress;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ModuleInterface $module;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getConstructionProgress(): ConstructionProgressInterface
    {
        return $this->progress;
    }

    #[Override]
    public function setConstructionProgress(ConstructionProgressInterface $progress): ConstructionProgressModuleInterface
    {
        $this->progress = $progress;
        return $this;
    }

    #[Override]
    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    #[Override]
    public function setModule(ModuleInterface $module): ConstructionProgressModuleInterface
    {
        $this->module = $module;

        return $this;
    }
}
