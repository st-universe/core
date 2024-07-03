<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\MapRepository;
use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Component\Map\MapEnum;
use Stu\Lib\SectorString;

#[Table(name: 'stu_map')]
#[Index(name: 'coordinates_idx', columns: ['cx', 'cy'])]
#[Index(name: 'coordinates_reverse_idx', columns: ['cy', 'cx'])]
#[Index(name: 'map_field_type_idx', columns: ['field_id'])]
#[Index(name: 'map_layer_idx', columns: ['layer_id'])]
#[Index(name: 'map_system_idx', columns: ['systems_id'])]
#[Index(name: 'map_system_type_idx', columns: ['system_type_id'])]
#[Index(name: 'map_influence_area_idx', columns: ['influence_area_id'])]
#[Index(name: 'map_bordertype_idx', columns: ['bordertype_id'])]
#[Index(name: 'map_admin_region_idx', columns: ['admin_region_id'])]
#[UniqueConstraint(name: 'map_coordinate_idx', columns: ['layer_id', 'cx', 'cy'])]
#[Entity(repositoryClass: MapRepository::class)]
class Map implements MapInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $cx = 0;

    #[Column(type: 'integer')]
    private int $cy = 0;

    #[Column(type: 'integer')]
    private int $layer_id;

    #[Column(type: 'integer')]
    private int $field_id = 0;

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

    #[ManyToOne(targetEntity: 'Layer')]
    #[JoinColumn(name: 'layer_id', referencedColumnName: 'id')]
    private LayerInterface $layer;

    #[OneToOne(targetEntity: 'StarSystem', inversedBy: 'map')]
    #[JoinColumn(name: 'systems_id', referencedColumnName: 'id')]
    private ?StarSystemInterface $starSystem = null;

    #[ManyToOne(targetEntity: 'StarSystem')]
    #[JoinColumn(name: 'influence_area_id', referencedColumnName: 'id')]
    private ?StarSystemInterface $influenceArea = null;

    #[ManyToOne(targetEntity: 'MapFieldType')]
    #[JoinColumn(name: 'field_id', referencedColumnName: 'id')]
    private MapFieldTypeInterface $mapFieldType;

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
     * @var ArrayCollection<int, BuoyInterface>
     */
    #[OneToMany(targetEntity: 'Buoy', mappedBy: 'map')]
    private Collection $buoys;

    /**
     * @var ArrayCollection<int, ShipInterface>
     */
    #[OneToMany(targetEntity: 'Ship', mappedBy: 'map', fetch: 'EXTRA_LAZY')]
    private Collection $ships;

    /**
     * @var ArrayCollection<int, FlightSignatureInterface>
     */
    #[OneToMany(targetEntity: 'FlightSignature', mappedBy: 'map')]
    #[OrderBy(['time' => 'DESC'])]
    private Collection $signatures;

    /**
     * @var ArrayCollection<int, AnomalyInterface>
     */
    #[OneToMany(targetEntity: 'Anomaly', mappedBy: 'map', fetch: 'EXTRA_LAZY')]
    private Collection $anomalies;

    /**
     * @var ArrayCollection<int, WormholeEntryInterface>
     */
    #[OneToMany(targetEntity: 'WormholeEntry', mappedBy: 'map')]
    private Collection $wormholeEntries;


    public function __construct()
    {
        $this->ships = new ArrayCollection();
        $this->signatures = new ArrayCollection();
        $this->anomalies = new ArrayCollection();
        $this->wormholeEntries = new ArrayCollection();
        $this->buoys = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getCx(): int
    {
        return $this->cx;
    }

    #[Override]
    public function setCx(int $cx): MapInterface
    {
        $this->cx = $cx;
        return $this;
    }

    #[Override]
    public function getX(): int
    {
        return $this->getCx();
    }

    #[Override]
    public function getCy(): int
    {
        return $this->cy;
    }

    #[Override]
    public function setCy(int $cy): MapInterface
    {
        $this->cy = $cy;
        return $this;
    }

    #[Override]
    public function getY(): int
    {
        return $this->getCy();
    }

    #[Override]
    public function getFieldId(): int
    {
        return $this->field_id;
    }

    #[Override]
    public function setFieldId(int $fieldId): MapInterface
    {
        $this->field_id = $fieldId;
        return $this;
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
    public function getLayer(): LayerInterface
    {
        return $this->layer;
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
    public function getFieldType(): MapFieldTypeInterface
    {
        return $this->mapFieldType;
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

    #[Override]
    public function getShips(): Collection
    {
        return $this->ships
            ->filter(fn (ShipInterface $ship): bool => $ship->getStarsystemMap() === null);
    }

    #[Override]
    public function getAnomalies(): Collection
    {
        return $this->anomalies;
    }

    #[Override]
    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    #[Override]
    public function getRandomWormholeEntry(): ?WormholeEntryInterface
    {
        if ($this->wormholeEntries->isEmpty()) {
            return null;
        }

        $usableEntries =  array_filter(
            $this->wormholeEntries->toArray(),
            function (WormholeEntryInterface $entry): bool {
                $type = $entry->getType();

                return $entry->isUsable() && ($type === MapEnum::WORMHOLE_ENTRY_TYPE_BOTH ||
                    $type === MapEnum::WORMHOLE_ENTRY_TYPE_IN);
            }
        );

        return $usableEntries === [] ? null : $usableEntries[array_rand($usableEntries)];
    }

    #[Override]
    public function getSectorString(): string
    {
        return SectorString::getForMap($this);
    }

    /**
     * @return Collection<int, BuoyInterface>
     */
    #[Override]
    public function getBuoys(): Collection
    {
        return $this->buoys;
    }
}
