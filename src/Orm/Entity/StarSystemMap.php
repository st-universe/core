<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
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
 * @Entity(repositoryClass="Stu\Orm\Repository\StarSystemMapRepository")
 * @Table(
 *     name="stu_sys_map",
 *     uniqueConstraints={@UniqueConstraint(name="system_coordinates_idx", columns={"sx", "sy", "systems_id"})}
 * )
 **/
class StarSystemMap implements StarSystemMapInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @Column(type="smallint")
     *
     */
    private int $sx = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $sy = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $systems_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $field_id = 0;

    /**
     *
     * @ManyToOne(targetEntity="StarSystem", inversedBy="fields")
     * @JoinColumn(name="systems_id", referencedColumnName="id")
     */
    private StarSystemInterface $starSystem;

    /**
     * @OneToOne(targetEntity="Colony", mappedBy="starsystem_map")
     */
    private ?ColonyInterface $colony = null;

    /**
     *
     * @ManyToOne(targetEntity="MapFieldType")
     * @JoinColumn(name="field_id", referencedColumnName="id")
     */
    private MapFieldTypeInterface $mapFieldType;

    /**
     * @var ArrayCollection<int, ShipInterface>
     *
     * @OneToMany(targetEntity="Ship", mappedBy="starsystem_map", fetch="EXTRA_LAZY")
     */
    private Collection $ships;

    /**
     * @var ArrayCollection<int, FlightSignatureInterface>
     *
     * @OneToMany(targetEntity="FlightSignature", mappedBy="starsystem_map")
     * @OrderBy({"time": "DESC"})
     */
    private Collection $signatures;

    /**
     * @var ArrayCollection<int, AnomalyInterface>
     *
     * @OneToMany(targetEntity="Anomaly", mappedBy="starsystem_map", fetch="EXTRA_LAZY")
     */
    private Collection $anomalies;

    /**
     * @var ArrayCollection<int, WormholeEntryInterface>
     *
     * @OneToMany(targetEntity="WormholeEntry", mappedBy="systemMap")
     */
    private Collection $wormholeEntries;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
        $this->signatures = new ArrayCollection();
        $this->anomalies = new ArrayCollection();
        $this->wormholeEntries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSx(): int
    {
        return $this->sx;
    }

    public function setSx(int $sx): StarSystemMapInterface
    {
        $this->sx = $sx;

        return $this;
    }

    public function getX(): int
    {
        return $this->getSx();
    }

    public function getSy(): int
    {
        return $this->sy;
    }

    public function setSy(int $sy): StarSystemMapInterface
    {
        $this->sy = $sy;

        return $this;
    }

    public function getY(): int
    {
        return $this->getSy();
    }

    public function getSystemId(): int
    {
        return $this->systems_id;
    }

    public function getSystem(): StarSystemInterface
    {
        return $this->starSystem;
    }

    public function setSystem(StarSystemInterface $starSystem): StarSystemMapInterface
    {
        $this->starSystem = $starSystem;

        return $this;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getFieldType(): MapFieldTypeInterface
    {
        return $this->mapFieldType;
    }

    public function setFieldType(MapFieldTypeInterface $mapFieldType): StarSystemMapInterface
    {
        $this->mapFieldType = $mapFieldType;

        return $this;
    }

    public function getBackgroundId(): string
    {

        $x = (string)$this->getSx();
        $y = (string)$this->getSy();


        $x = str_pad($x, 2, '0', STR_PAD_LEFT);
        $y = str_pad($y, 2, '0', STR_PAD_LEFT);


        $backgroundId = $y . $x;

        return $backgroundId;
    }



    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    public function getMapRegion(): ?MapRegionInterface
    {
        return null;
    }

    public function getAdministratedRegion(): ?MapRegionInterface
    {
        return null;
    }

    public function getInfluenceArea(): ?StarSystemInterface
    {
        return null;
    }

    public function getFieldStyle(): string
    {
        return "background-image: url('/assets/map/" . $this->getFieldId() . ".png'); opacity:1;";
    }

    public function getFieldGraphicID(): int
    {
        $fieldId = $this->getFieldId();

        if ($fieldId === 1) {
            return 0;
        } else {
            return $fieldId;
        }
    }


    public function getShips(): Collection
    {
        return $this->ships;
    }

    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    public function getAnomalies(): Collection
    {
        return $this->anomalies;
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
                    $type === MapEnum::WORMHOLE_ENTRY_TYPE_OUT);
            }
        );

        return $usableEntries === [] ? null : $usableEntries[array_rand($usableEntries)];
    }

    public function getSectorString(): string
    {
        return SectorString::getForStarSystemMap($this);
    }
}
