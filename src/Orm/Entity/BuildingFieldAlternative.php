<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $fieldtype = 0;

    /** @Column(type="integer") * */
    private $buildings_id = 0;

    /** @Column(type="integer") * */
    private $alternate_buildings_id = 0;

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

    public function getAlternativeBuilding(): \BuildingData
    {
        return ResourceCache()->getObject(CACHE_BUILDING, $this->getAlternativeBuildingId());
    }

    public function getBuilding(): \BuildingData
    {
        return ResourceCache()->getObject(CACHE_BUILDING, $this->getBuildingId());
    }
}
