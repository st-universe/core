<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\WeaponRepository;

#[Table(name: 'stu_weapons')]
#[Index(name: 'weapon_module_idx', columns: ['module_id'])]
#[Entity(repositoryClass: WeaponRepository::class)]
class Weapon
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

    #[OneToOne(targetEntity: Module::class, inversedBy: 'weapon')]
    #[JoinColumn(name: 'module_id', nullable: false, referencedColumnName: 'id')]
    private Module $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Weapon
    {
        $this->name = $name;

        return $this;
    }

    public function getVariance(): int
    {
        return $this->variance;
    }

    public function setVariance(int $variance): Weapon
    {
        $this->variance = $variance;

        return $this;
    }

    public function getCriticalChance(): int
    {
        return $this->critical_chance;
    }

    public function setCriticalChance(int $criticalChance): Weapon
    {
        $this->critical_chance = $criticalChance;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): Weapon
    {
        $this->type = $type;

        return $this;
    }

    public function getFiringMode(): int
    {
        return $this->firing_mode;
    }

    public function setFiringMode(int $firingMode): Weapon
    {
        $this->firing_mode = $firingMode;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): Weapon
    {
        $this->module_id = $moduleId;

        return $this;
    }
}
