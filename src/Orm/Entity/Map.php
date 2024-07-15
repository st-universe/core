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
use Override;
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
class Map extends Location implements MapInterface
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

    #[OneToOne(targetEntity: 'StarSystem', inversedBy: 'map')]
    #[JoinColumn(name: 'systems_id', referencedColumnName: 'id')]
    private ?StarSystemInterface $starSystem = null;

    #[ManyToOne(targetEntity: 'StarSystem')]
    #[JoinColumn(name: 'influence_area_id', referencedColumnName: 'id')]
    private ?StarSystemInterface $influenceArea = null;

    #[ManyToOne(targetEntity: 'StarSystemType')]
    #[JoinColumn(name: 'system_type_id', referencedColumnName: 'id')]
    private ?StarSystemTypeInterface $starSystemType = null;

    #[ManyToOne(targetEntity: 'MapBorderType')]
    #[JoinColumn(name: 'bordertype_id', referencedColumnName: 'id')]
    private ?MapBorderTypeInterface $mapBorderType = null;

    #[ManyToOne(targetEntity: 'MapRegion')]
    #[JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    private ?MapRegionInterface $mapRegion = null;

    #[ManyToOne(targetEntity: 'MapRegion')]
    #[JoinColumn(name: 'admin_region_id', referencedColumnName: 'id')]
    private ?MapRegionInterface $administratedRegion = null;

    /**
     * @var ArrayCollection<int, WormholeEntryInterface>
     */
    #[OneToMany(targetEntity: 'WormholeEntry', mappedBy: 'map')]
    private Collection $wormholeEntries;

    public function __construct()
    {
        parent::__construct();
        $this->wormholeEntries = new ArrayCollection();
    }

    #[Override]
    public function getLayer(): ?LayerInterface
    {
        if ($this->layer === null) {
            throw new RuntimeException('Layer of Map can not be null');
        }
        return $this->layer;
    }

    #[Override]
    public function getX(): int
    {
        if ($this->getCx() === null) {
            throw new RuntimeException('Cx of Map can not be null');
        }
        return $this->getCx();
    }

    #[Override]
    public function getY(): int
    {
        if ($this->getCy() === null) {
            throw new RuntimeException('Cy of Map can not be null');
        }
        return $this->getCy();
    }

    #[Override]
    public function getSystemsId(): ?int
    {
        return $this->systems_id;
    }

    #[Override]
    public function setSystemsId(?int $systems_id): MapInterface
    {
        $this->systems_id = $systems_id;
        return $this;
    }

    #[Override]
    public function getSystemTypeId(): ?int
    {
        return $this->system_type_id;
    }

    #[Override]
    public function setSystemTypeId(?int $system_type_id): MapInterface
    {
        $this->system_type_id = $system_type_id;
        return $this;
    }

    #[Override]
    public function getInfluenceAreaId(): ?int
    {
        return $this->influence_area_id;
    }

    #[Override]
    public function setInfluenceAreaId(?int $influenceAreaId): MapInterface
    {
        $this->influence_area_id = $influenceAreaId;
        return $this;
    }

    #[Override]
    public function getBordertypeId(): ?int
    {
        return $this->bordertype_id;
    }

    #[Override]
    public function setBordertypeId(?int $bordertype_id): MapInterface
    {
        $this->bordertype_id = $bordertype_id;
        return $this;
    }

    #[Override]
    public function getRegionId(): ?int
    {
        return $this->region_id;
    }

    #[Override]
    public function setRegionId(?int $region_id): MapInterface
    {
        $this->region_id = $region_id;
        return $this;
    }

    #[Override]
    public function getAdminRegionId(): ?int
    {
        return $this->admin_region_id;
    }

    #[Override]
    public function setAdminRegionId(?int $admin_region_id): MapInterface
    {
        $this->admin_region_id = $admin_region_id;
        return $this;
    }

    #[Override]
    public function getSystem(): ?StarSystemInterface
    {
        return $this->starSystem;
    }

    #[Override]
    public function setSystem(StarSystemInterface $starSystem): MapInterface
    {
        $this->starSystem = $starSystem;
        return $this;
    }

    #[Override]
    public function getInfluenceArea(): ?StarSystemInterface
    {
        return $this->influenceArea;
    }

    #[Override]
    public function setInfluenceArea(?StarSystemInterface $influenceArea): MapInterface
    {
        $this->influenceArea = $influenceArea;
        return $this;
    }

    #[Override]
    public function getStarSystemType(): ?StarSystemTypeInterface
    {
        return $this->starSystemType;
    }

    #[Override]
    public function getMapBorderType(): ?MapBorderTypeInterface
    {
        return $this->mapBorderType;
    }

    #[Override]
    public function getMapRegion(): ?MapRegionInterface
    {
        return $this->mapRegion;
    }

    #[Override]
    public function getAdministratedRegion(): ?MapRegionInterface
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

    protected function getWormholeEntries(): Collection
    {
        return $this->wormholeEntries;
    }

    #[Override]
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

    #[Override]
    public function getSectorString(): string
    {
        return  $this->getCx() . '|' . $this->getCy();
    }
}
