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
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingFieldAlternativeRepository")
 * @Table(
 *     name="stu_buildings_field_alternative",
 *     indexes={
 *          @Index(name="building_field_idx", columns={"fieldtype", "buildings_id"})
 * })
 **/
class BuildingFieldAlternative implements BuildingFieldAlternativeInterface
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
    private $fieldtype = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $buildings_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $alternate_buildings_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var null|int
     */
    private $research_id;

    /**
     * @var BuildingInterface
     *
     * @ManyToOne(targetEntity="Building")
     * @JoinColumn(name="buildings_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $building;

    /**
     * @var BuildingInterface
     *
     * @ManyToOne(targetEntity="Building")
     * @JoinColumn(name="alternate_buildings_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $alternateBuilding;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFieldType(): int
    {
        return $this->fieldtype;
    }

    public function setFieldType(int $fieldType): BuildingFieldAlternativeInterface
    {
        $this->fieldtype = $fieldType;

        return $this;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): BuildingFieldAlternativeInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getAlternativeBuildingId(): int
    {
        return $this->alternate_buildings_id;
    }

    public function setAlternativeBuildingId(int $alternativeBuildingId): BuildingFieldAlternativeInterface
    {
        $this->alternate_buildings_id = $alternativeBuildingId;

        return $this;
    }

    public function getAlternativeBuilding(): BuildingInterface
    {
        return $this->alternateBuilding;
    }

    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    public function setResearchId(?int $researchId): BuildingFieldAlternativeInterface
    {
        $this->research_id = $researchId;
        return $this;
    }

    public function getBuilding(): BuildingInterface
    {
        return $this->building;
    }
}
