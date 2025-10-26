<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use RuntimeException;
use Stu\Component\Map\MapEnum;
use Stu\Orm\Repository\MapRepository;

#[Table(name: 'stu_map')]
#[Index(name: 'map_system_idx', columns: ['systems_id'])]
#[Index(name: 'map_system_type_idx', columns: ['system_type_id'])]
#[Index(name: 'map_influence_area_idx', columns: ['influence_area_id'])]
#[Index(name: 'map_bordertype_idx', columns: ['bordertype_id'])]
#[Index(name: 'map_admin_region_idx', columns: ['admin_region_id'])]
#[Entity(repositoryClass: MapRepository::class)]
class Map extends Location
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $system_type_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $systems_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $influence_area_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $bordertype_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $region_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $admin_region_id = null;

    #[OneToOne(targetEntity: StarSystem::class, inversedBy: 'map')]
    #[JoinColumn(name: 'systems_id', referencedColumnName: 'id')]
    private ?StarSystem $starSystem = null;

    #[ManyToOne(targetEntity: StarSystem::class)]
    #[JoinColumn(name: 'influence_area_id', referencedColumnName: 'id')]
    private ?StarSystem $influenceArea = null;

    #[ManyToOne(targetEntity: StarSystemType::class)]
    #[JoinColumn(name: 'system_type_id', referencedColumnName: 'id')]
    private ?StarSystemType $starSystemType = null;

    #[ManyToOne(targetEntity: MapBorderType::class)]
    #[JoinColumn(name: 'bordertype_id', referencedColumnName: 'id')]
    private ?MapBorderType $mapBorderType = null;

    #[ManyToOne(targetEntity: MapRegion::class)]
    #[JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    private ?MapRegion $mapRegion = null;

    #[ManyToOne(targetEntity: MapRegion::class)]
    #[JoinColumn(name: 'admin_region_id', referencedColumnName: 'id')]
    private ?MapRegion $administratedRegion = null;

    /**
     * @var ArrayCollection<int, WormholeEntry>
     */
    #[OneToMany(targetEntity: WormholeEntry::class, mappedBy: 'map')]
    private Collection $wormholeEntries;

    public function __construct()
    {
        parent::__construct();
        $this->wormholeEntries = new ArrayCollection();
    }

    #[\Override]
    public function getLayer(): ?Layer
    {
        if ($this->layer === null) {
            throw new RuntimeException('Layer of Map can not be null');
        }
        return $this->layer;
    }

    #[\Override]
    public function getX(): int
    {
        if ($this->getCx() === null) {
            throw new RuntimeException('Cx of Map can not be null');
        }
        return $this->getCx();
    }

    #[\Override]
    public function getY(): int
    {
        if ($this->getCy() === null) {
            throw new RuntimeException('Cy of Map can not be null');
        }
        return $this->getCy();
    }

    public function getSystemTypeId(): ?int
    {
        return $this->system_type_id;
    }

    public function setSystemTypeId(?int $system_type_id): Map
    {
        $this->system_type_id = $system_type_id;
        return $this;
    }

    public function getInfluenceAreaId(): ?int
    {
        return $this->influence_area_id;
    }

    public function setInfluenceAreaId(?int $influenceAreaId): Map
    {
        $this->influence_area_id = $influenceAreaId;
        return $this;
    }

    public function getBordertypeId(): ?int
    {
        return $this->bordertype_id;
    }

    public function setBordertypeId(?int $bordertype_id): Map
    {
        $this->bordertype_id = $bordertype_id;
        return $this;
    }

    public function getRegionId(): ?int
    {
        return $this->region_id;
    }

    public function setRegionId(?int $region_id): Map
    {
        $this->region_id = $region_id;
        return $this;
    }

    public function getAdminRegionId(): ?int
    {
        return $this->admin_region_id;
    }

    public function setAdminRegionId(?int $admin_region_id): Map
    {
        $this->admin_region_id = $admin_region_id;
        return $this;
    }

    public function getSystem(): ?StarSystem
    {
        return $this->starSystem;
    }

    public function setSystem(StarSystem $starSystem): Map
    {
        $this->starSystem = $starSystem;
        return $this;
    }

    public function getInfluenceArea(): ?StarSystem
    {
        return $this->influenceArea;
    }

    public function setInfluenceArea(?StarSystem $influenceArea): Map
    {
        $this->influenceArea = $influenceArea;
        return $this;
    }

    public function getStarSystemType(): ?StarSystemType
    {
        return $this->starSystemType;
    }

    public function getMapBorderType(): ?MapBorderType
    {
        return $this->mapBorderType;
    }

    public function getMapRegion(): ?MapRegion
    {
        return $this->mapRegion;
    }

    public function getAdministratedRegion(): ?MapRegion
    {
        return $this->administratedRegion;
    }

    public function getBorder(): string
    {
        $borderType = $this->getMapBorderType();
        if ($borderType === null) {
            return '';
        }
        return 'border: 1px solid ' . $borderType->getColor();
    }

    public function getBorderColor(): string
    {
        $borderType = $this->getMapBorderType();
        if ($borderType === null) {
            return '';
        }
        return $borderType->getColor();
    }

    #[\Override]
    protected function getWormholeEntries(): Collection
    {
        return $this->wormholeEntries;
    }

    public function getSectorId(): ?int
    {
        $layer = $this->getLayer();
        if ($layer === null) {
            throw new RuntimeException('Layer of Map can not be null');
        }

        $cx = $this->getCx();
        $cy = $this->getCy();
        if ($cx === null || $cy === null) {
            throw new RuntimeException('Cx and Cy of Map can not be null');
        }

        return $layer->getSectorId(
            (int) ceil($cx / MapEnum::FIELDS_PER_SECTION),
            (int) ceil($cy / MapEnum::FIELDS_PER_SECTION)
        );
    }

    #[\Override]
    public function getSectorString(): string
    {
        return $this->getCx() . '|' . $this->getCy();
    }
}
