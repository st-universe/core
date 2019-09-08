<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\MapRepository")
 * @Table(
 *     name="stu_map",
 *     indexes={
 *         @Index(name="coordinates_idx", columns={"cx","cy"})
 *     }
 * )
 **/
class Map implements MapInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $cx = 0;

    /** @Column(type="integer") * */
    private $cy = 0;

    /** @Column(type="integer") * */
    private $field_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $systems_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $bordertype_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $region_id = 0;

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

    /**
     * @ManyToOne(targetEntity="MapBorderType")
     * @JoinColumn(name="bordertype_id", referencedColumnName="id")
     */
    private $mapBorderType;

    /**
     * @ManyToOne(targetEntity="MapRegion")
     * @JoinColumn(name="region_id", referencedColumnName="id")
     */
    private $mapRegion;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCx(): int
    {
        return $this->cx;
    }

    public function setCx(int $cx): MapInterface
    {
        $this->cx = $cx;
        return $this;
    }

    public function getCy(): int
    {
        return $this->cy;
    }

    public function setCy(int $cy): MapInterface
    {
        $this->cy = $cy;
        return $this;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function setFieldId(int $fieldId): MapInterface
    {
        $this->field_id = $fieldId;
        return $this;
    }

    public function getSystemsId(): ?int
    {
        return $this->systems_id;
    }

    public function setSystemsId(?int $systems_id): MapInterface
    {
        $this->systems_id = $systems_id;
        return $this;
    }

    public function getBordertypeId(): ?int
    {
        return $this->bordertype_id;
    }

    public function setBordertypeId(?int $bordertype_id): MapInterface
    {
        $this->bordertype_id = $bordertype_id;
        return $this;
    }

    public function getRegionId(): int
    {
        return $this->region_id;
    }

    public function setRegionId(?int $region_id): MapInterface
    {
        $this->region_id = $region_id;
        return $this;
    }

    public function getSystem(): ?StarSystemInterface
    {
        return $this->starSystem;
    }

    public function getFieldType(): MapFieldTypeInterface
    {
        return $this->mapFieldType;
    }

    public function getMapBorderType(): ?MapBorderTypeInterface
    {
        return $this->mapBorderType;
    }

    public function getMapRegion(): ?MapRegionInterface
    {
        return $this->mapRegion;
    }

    public function getBorder(): string
    {
        $borderType = $this->getMapBorderType();
        if ($borderType === null) {
            return '';
        }
        return 'border: 1px solid #' . $borderType->getColor();
    }

    public function getFieldStyle(): string
    {
        // @todo hide unexplored fields
        $style = "background-image: url('assets/map/" . $this->getFieldId() . ".gif');";
        $style .= $this->getBorder();
        return $style;
    }
}
