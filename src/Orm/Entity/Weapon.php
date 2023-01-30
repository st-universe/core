<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\WeaponRepository")
 * @Table(
 *     name="stu_weapons",
 *     indexes={
 *         @Index(name="weapon_module_idx", columns={"module_id"})
 *     }
 * )
 **/
class Weapon implements WeaponInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $name = '';

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $variance = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $critical_chance = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $type = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $firing_mode = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $module_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): WeaponInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getVariance(): int
    {
        return $this->variance;
    }

    public function setVariance(int $variance): WeaponInterface
    {
        $this->variance = $variance;

        return $this;
    }

    public function getCriticalChance(): int
    {
        return $this->critical_chance;
    }

    public function setCriticalChance(int $criticalChance): WeaponInterface
    {
        $this->critical_chance = $criticalChance;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): WeaponInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getFiringMode(): int
    {
        return $this->firing_mode;
    }

    public function setFiringMode(int $firingMode): WeaponInterface
    {
        $this->firing_mode = $firingMode;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): WeaponInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }
}
