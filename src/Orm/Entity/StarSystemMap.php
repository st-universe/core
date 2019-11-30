<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StarSystemMapRepository")
 * @Table(
 *     name="stu_sys_map",
 *     uniqueConstraints={@UniqueConstraint(name="system_coordinates_idx", columns={"sx", "sy", "systems_id"})}
 * )
 **/
class StarSystemMap implements StarSystemMapInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="smallint") * */
    private $sx = 0;

    /** @Column(type="smallint") * */
    private $sy = 0;

    /** @Column(type="integer") * */
    private $systems_id = 0;

    /** @Column(type="integer") * */
    private $field_id = 0;

    /**
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="systems_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @ManyToOne(targetEntity="MapFieldType")
     * @JoinColumn(name="field_id", referencedColumnName="id")
     */
    private $mapFieldType;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSx(): int
    {
        return $this->sx;
    }

    public function setSx($sx): StarSystemMapInterface
    {
        $this->sx = $sx;

        return $this;
    }

    public function getSy(): int
    {
        return $this->sy;
    }

    public function setSy($sy): StarSystemMapInterface
    {
        $this->sy = $sy;

        return $this;
    }

    public function getSystemId(): int
    {
        return $this->systems_id;
    }

    public function setSystemId(int $systemId): StarSystemMapInterface
    {
        $this->systems_id = $systemId;

        return $this;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function setFieldId(int $fieldId): StarSystemMapInterface
    {
        $this->field_id = $fieldId;

        return $this;
    }

    public function getFieldType(): MapFieldTypeInterface
    {
        return $this->mapFieldType;
    }

    public function getMapRegion(): ?MapRegionInterface
    {
        return null;
    }

    public function getFieldStyle(): string
    {
        return "background-image: url('/assets/map/" . $this->getFieldId() . ".gif');";
    }
}
