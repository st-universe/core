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
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\MapFieldTypeRepository")
 * @Table(
 *     name="stu_map_ftypes",
 *     indexes={
 *          @Index(name="map_ftypes_type_idx", columns={"type"})
 *     }
 * )
 **/
class MapFieldType implements MapFieldTypeInterface
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
     * @Column(type="integer")
     *
     * @var int
     */
    private $type = 0;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $is_system = false;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $ecost = 0;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $name = '';

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $colonies_classes_id = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $damage = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $x_damage = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $x_damage_system = 0;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $view = false;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $passable = false;

    /**
     * @var null|ColonyClassInterface
     *
     * @ManyToOne(targetEntity="ColonyClass")
     * @JoinColumn(name="colonies_classes_id", referencedColumnName="id")
     */
    private $colonyClass;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): MapFieldTypeInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getIsSystem(): bool
    {
        return $this->is_system;
    }

    public function setIsSystem(bool $isSystem): MapFieldTypeInterface
    {
        $this->is_system = $isSystem;

        return $this;
    }

    public function getEnergyCosts(): int
    {
        return $this->ecost;
    }

    public function setEnergyCosts(int $energyCosts): MapFieldTypeInterface
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MapFieldTypeInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getColonyClassId(): int
    {
        return $this->colonies_classes_id;
    }

    public function setColonyClassId(int $colonyClassId): MapFieldTypeInterface
    {
        $this->colonies_classes_id = $colonyClassId;

        return $this;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): MapFieldTypeInterface
    {
        $this->damage = $damage;

        return $this;
    }

    public function getSpecialDamage(): int
    {
        return $this->x_damage;
    }

    public function setSpecialDamage(int $specialDamage): MapFieldTypeInterface
    {
        $this->x_damage = $specialDamage;

        return $this;
    }

    public function getSpecialDamageInnerSystem(): int
    {
        return $this->x_damage_system;
    }

    public function setSpecialDamageInnerSystem(int $specialDamageInnerSystem): MapFieldTypeInterface
    {
        $this->x_damage_system = $specialDamageInnerSystem;

        return $this;
    }

    public function getView(): bool
    {
        return $this->view;
    }

    public function setView(bool $view): MapFieldTypeInterface
    {
        $this->view = $view;

        return $this;
    }

    public function getPassable(): bool
    {
        return $this->passable;
    }

    public function setPassable(bool $passable): MapFieldTypeInterface
    {
        $this->passable = $passable;

        return $this;
    }

    public function getColonyClass(): ?ColonyClassInterface
    {
        return $this->colonyClass;
    }
}
