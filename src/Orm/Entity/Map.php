<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\MapRepository")
 * @Table(
 *     name="stu_map",
 *     indexes={
 *         @Index(name="coordinates_idx", columns={"cx", "cy"}),
 *         @Index(name="coordinates_reverse_idx", columns={"cy", "cx"}),
 *         @Index(name="map_field_type_idx", columns={"field_id"}),
 *         @Index(name="map_layer_idx", columns={"layer_id"}),
 *         @Index(name="map_system_idx", columns={"systems_id"})
 *     },
 *     uniqueConstraints={
 *         @UniqueConstraint(name="map_coordinate_idx", columns={"layer_id", "cx", "cy"})
 *     }
 * )
 **/
class Map implements MapInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $cx = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $cy = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $layer_id;

    /**
     * @Column(type="integer")
     *
     */
    private int $field_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $systems_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $influence_area_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $bordertype_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $region_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $admin_region_id;

    /**
     * @var LayerInterface
     *
     * @ManyToOne(targetEntity="Layer")
     * @JoinColumn(name="layer_id", referencedColumnName="id")
     */
    private $layer;

    /**
     * @var null|StarSystemInterface
     *
     * @OneToOne(targetEntity="StarSystem", inversedBy="map")
     * @JoinColumn(name="systems_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @var null|StarSystemInterface
     *
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="influence_area_id", referencedColumnName="id")
     */
    private $influenceArea;

    /**
     * @var MapFieldTypeInterface
     *
     * @ManyToOne(targetEntity="MapFieldType")
     * @JoinColumn(name="field_id", referencedColumnName="id")
     */
    private $mapFieldType;

    /**
     * @var null|MapBorderTypeInterface
     *
     * @ManyToOne(targetEntity="MapBorderType")
     * @JoinColumn(name="bordertype_id", referencedColumnName="id")
     */
    private $mapBorderType;

    /**
     * @var null|MapRegionInterface
     *
     * @ManyToOne(targetEntity="MapRegion")
     * @JoinColumn(name="region_id", referencedColumnName="id")
     */
    private $mapRegion;

    /**
     * @var null|MapRegionInterface
     *
     * @ManyToOne(targetEntity="MapRegion")
     * @JoinColumn(name="admin_region_id", referencedColumnName="id")
     */
    private $administratedRegion;

    /**
     * @var ArrayCollection<int, ShipInterface>
     *
     * @OneToMany(targetEntity="Ship", mappedBy="map", fetch="EXTRA_LAZY")
     */
    private $ships;

    /**
     * @var ArrayCollection<int, FlightSignatureInterface>
     *
     * @OneToMany(targetEntity="FlightSignature", mappedBy="map")
     * @OrderBy({"time": "DESC"})
     */
    private $signatures;

    /**
     * @var ArrayCollection<int, WormholeEntryInterface>
     *
     * @OneToMany(targetEntity="WormholeEntry", mappedBy="map")
     */
    private $wormholeEntries;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
        $this->signatures = new ArrayCollection();
        $this->wormholeEntries = new ArrayCollection();
    }

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

    public function getInfluenceAreaId(): ?int
    {
        return $this->influence_area_id;
    }

    public function setInfluenceAreaId(?int $influenceAreaId): MapInterface
    {
        $this->influence_area_id = $influenceAreaId;
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

    public function getLayer(): LayerInterface
    {
        return $this->layer;
    }

    public function getSystem(): ?StarSystemInterface
    {
        return $this->starSystem;
    }

    public function getInfluenceArea(): ?StarSystemInterface
    {
        return $this->influenceArea;
    }

    public function setInfluenceArea(?StarSystemInterface $influenceArea): MapInterface
    {
        $this->influenceArea = $influenceArea;
        return $this;
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

    public function getFieldStyle(): string
    {
        // @todo hide unexplored fields
        $style = "background-image: url('/assets/map/" . $this->getLayer()->getId() . "/" . $this->getFieldId() . ".png'); transform: translateZ(0);";
        $style .= $this->getBorder();
        return $style;
    }

    public function getShips(): Collection
    {
        return $this->ships;
    }

    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

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

        return empty($usableEntries) ? null : $usableEntries[array_rand($usableEntries)];
    }

    public function getSectorString(): string
    {
        return SectorString::getForMap($this);
    }
}
