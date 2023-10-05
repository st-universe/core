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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\MapFieldTypeRepository")
 * @Table(
 *     name="stu_map_ftypes",
 *     indexes={
 *         @Index(name="map_ftypes_type_idx", columns={"type"})
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
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $type = 0;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $is_system = false;

    /**
     * @Column(type="smallint")
     *
     */
    private int $ecost = 0;

    /**
     * @Column(type="string")
     *
     */
    private string $name = '';

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $colonies_classes_id = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $damage = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $x_damage = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $x_damage_system = 0;

    /**
     * @Column(type="smallint", nullable=true)
     *
     */
    private ?int $x_damage_type = null;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $view = false;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $passable = false;

    /**
     *
     * @ManyToOne(targetEntity="ColonyClass")
     * @JoinColumn(name="colonies_classes_id", referencedColumnName="id")
     */
    private ?ColonyClassInterface $colonyClass = null;

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

    public function getSpecialDamageType(): ?int
    {
        return $this->x_damage_type;
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

    public function getPassableAsInt(): int
    {
        return $this->passable ? 1 : 0;
    }

    public function getColonyClass(): ?ColonyClassInterface
    {
        return $this->colonyClass;
    }
}
