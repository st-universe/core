<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
     */
    private $id;

    /** @Column(type="integer") * */
    private $type = 0;

    /** @Column(type="boolean") * */
    private $is_system = false;

    /** @Column(type="smallint") * */
    private $ecost = 0;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="integer", nullable=true) * */
    private $colonies_classes_id = 0;

    /** @Column(type="smallint") * */
    private $damage = 0;

    /** @Column(type="smallint") * */
    private $x_damage = 0;

    /** @Column(type="smallint") * */
    private $x_damage_system = 0;

    /** @Column(type="boolean") * */
    private $view = false;

    /** @Column(type="boolean") * */
    private $passable = false;

    /**
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

    public function setPassable(int $passable): MapFieldTypeInterface
    {
        $this->passable = $passable;

        return $this;
    }

    public function getColonyClass(): ?ColonyClassInterface
    {
        return $this->colonyClass;
    }
}
