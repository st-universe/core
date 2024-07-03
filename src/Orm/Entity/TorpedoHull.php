<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\TorpedoHullRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_torpedo_hull')]
#[Index(name: 'torpedo_hull_module_idx', columns: ['module_id'])]
#[Index(name: 'torpedo_hull_torpedo_idx', columns: ['torpedo_type'])]
#[Entity(repositoryClass: TorpedoHullRepository::class)]
class TorpedoHull implements TorpedoHullInterface
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


    #[ManyToOne(targetEntity: 'TorpedoType')]
    #[JoinColumn(name: 'torpedo_type', referencedColumnName: 'id')]
    private TorpedoTypeInterface $torpedo;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id')]
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
    public function setModuleId(int $moduleId): TorpedoHullInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getTorpedoType(): int
    {
        return $this->torpedo_type;
    }

    #[Override]
    public function setTorpedoType(int $torpedoType): TorpedoHullInterface
    {
        $this->torpedo_type = $torpedoType;

        return $this;
    }

    #[Override]
    public function getModificator(): int
    {
        return $this->modificator;
    }

    #[Override]
    public function setModificator(int $Modificator): TorpedoHullInterface
    {
        $this->modificator = $Modificator;

        return $this;
    }

    #[Override]
    public function getTorpedo(): TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    #[Override]
    public function getModule(): ModuleInterface
    {
        return $this->module;
    }
}
