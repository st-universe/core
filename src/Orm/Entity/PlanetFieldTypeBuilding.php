<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PlanetFieldTypeBuildingRepository")
 * @Table(
 *     name="stu_field_build",
 *     indexes={@Index(name="type_building_idx", columns={"type", "buildings_id"})}
 * )
 **/
class PlanetFieldTypeBuilding implements PlanetFieldTypeBuildingInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $type = 0;

    /** @Column(type="integer") * */
    private $buildings_id = 0;

    /**
     * @ManyToOne(targetEntity="Building", inversedBy="buildingPossibleFieldTypes")
     * @JoinColumn(name="buildings_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $building;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFieldTypeId(): int
    {
        return $this->type;
    }

    public function setFieldTypeId(int $fieldTypeId): PlanetFieldTypeBuildingInterface
    {
        $this->type = $fieldTypeId;

        return $this;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): PlanetFieldTypeBuildingInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getFieldTypeDescription(): string
    {
        // @todo remove
        global $container;

        return $container->get(PlanetFieldTypeRetrieverInterface::class)->getDescription($this->getFieldTypeId());
    }
}
