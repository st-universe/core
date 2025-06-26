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
use Stu\Orm\Repository\TorpedoHullRepository;

#[Table(name: 'stu_torpedo_hull')]
#[Index(name: 'torpedo_hull_module_idx', columns: ['module_id'])]
#[Index(name: 'torpedo_hull_torpedo_idx', columns: ['torpedo_type'])]
#[Entity(repositoryClass: TorpedoHullRepository::class)]
class TorpedoHull
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'integer')]
    private int $torpedo_type = 0;

    #[Column(type: 'integer')]
    private int $modificator = 0;


    #[ManyToOne(targetEntity: TorpedoType::class)]
    #[JoinColumn(name: 'torpedo_type', nullable: false, referencedColumnName: 'id')]
    private TorpedoType $torpedo;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', nullable: false, referencedColumnName: 'id')]
    private Module $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): TorpedoHull
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getTorpedoType(): int
    {
        return $this->torpedo_type;
    }

    public function setTorpedoType(int $torpedoType): TorpedoHull
    {
        $this->torpedo_type = $torpedoType;

        return $this;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $Modificator): TorpedoHull
    {
        $this->modificator = $Modificator;

        return $this;
    }

    public function getTorpedo(): TorpedoType
    {
        return $this->torpedo;
    }

    public function getModule(): Module
    {
        return $this->module;
    }
}
