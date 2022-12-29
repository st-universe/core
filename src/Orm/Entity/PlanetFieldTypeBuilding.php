<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PlanetFieldTypeBuildingRepository")
 * @Table(
 *     name="stu_field_build",
 *     indexes={
 *          @Index(name="type_building_idx", columns={"type", "buildings_id"}),
 *          @Index(name="type_building_research_idx", columns={"type", "research_id"})
 *     }
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

    /** @Column(type="integer", nullable=true) * */
    private $research_id;

    /** @Column(type="boolean") * */
    private $view = true;

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

    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    public function setResearchId(?int $researchId): PlanetFieldTypeBuildingInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getView(): bool
    {
        return $this->view;
    }

    public function setView(bool $view): PlanetFieldTypeBuildingInterface
    {
        $this->view = $view;

        return $this;
    }

    public function getFieldTypeDescription(): string
    {
        // @todo remove
        global $container;

        return $container->get(PlanetFieldTypeRetrieverInterface::class)->getDescription($this->getFieldTypeId());
    }
}
