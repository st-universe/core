<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\WeaponRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_weapons')]
#[Index(name: 'weapon_module_idx', columns: ['module_id'])]
#[Entity(repositoryClass: WeaponRepository::class)]
class Weapon implements WeaponInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'smallint')]
    private int $variance = 0;

    #[Column(type: 'smallint')]
    private int $critical_chance = 0;

    #[Column(type: 'smallint')]
    private int $type = 0;

    #[Column(type: 'smallint')]
    private int $firing_mode = 0;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[OneToOne(targetEntity: 'Module', inversedBy: 'weapon')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id')]
    private ModuleInterface $module;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): WeaponInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getVariance(): int
    {
        return $this->variance;
    }

    #[Override]
    public function setVariance(int $variance): WeaponInterface
    {
        $this->variance = $variance;

        return $this;
    }

    #[Override]
    public function getCriticalChance(): int
    {
        return $this->critical_chance;
    }

    #[Override]
    public function setCriticalChance(int $criticalChance): WeaponInterface
    {
        $this->critical_chance = $criticalChance;

        return $this;
    }

    #[Override]
    public function getType(): int
    {
        return $this->type;
    }

    #[Override]
    public function setType(int $type): WeaponInterface
    {
        $this->type = $type;

        return $this;
    }

    #[Override]
    public function getFiringMode(): int
    {
        return $this->firing_mode;
    }

    #[Override]
    public function setFiringMode(int $firingMode): WeaponInterface
    {
        $this->firing_mode = $firingMode;

        return $this;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): WeaponInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }
}
