<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
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

#[Table(name: 'stu_sys_map')]
#[UniqueConstraint(name: 'system_coordinates_idx', columns: ['sx', 'sy', 'systems_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\StarSystemMapRepository')]
class StarSystemMap implements StarSystemMapInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint')]
    private int $sx = 0;

    #[Column(type: 'smallint')]
    private int $sy = 0;

    #[Column(type: 'integer')]
    private int $systems_id = 0;

    #[Column(type: 'integer')]
    private int $field_id = 0;

    #[ManyToOne(targetEntity: 'StarSystem', inversedBy: 'fields')]
    #[JoinColumn(name: 'systems_id', referencedColumnName: 'id')]
    private StarSystemInterface $starSystem;

    #[OneToOne(targetEntity: 'Colony', mappedBy: 'starsystem_map')]
    private ?ColonyInterface $colony = null;

    #[ManyToOne(targetEntity: 'MapFieldType')]
    #[JoinColumn(name: 'field_id', referencedColumnName: 'id')]
    private MapFieldTypeInterface $mapFieldType;

    /**
     * @var ArrayCollection<int, BuoyInterface>
     */
    #[OneToMany(targetEntity: 'Buoy', mappedBy: 'systemMap')]
    private Collection $buoys;

    /**
     * @var ArrayCollection<int, ShipInterface>
     */
    #[OneToMany(targetEntity: 'Ship', mappedBy: 'starsystem_map', fetch: 'EXTRA_LAZY')]
    private Collection $ships;

    /**
     * @var ArrayCollection<int, FlightSignatureInterface>
     */
    #[OneToMany(targetEntity: 'FlightSignature', mappedBy: 'starsystem_map')]
    #[OrderBy(['time' => 'DESC'])]
    private Collection $signatures;

    /**
     * @var ArrayCollection<int, AnomalyInterface>
     */
    #[OneToMany(targetEntity: 'Anomaly', mappedBy: 'starsystem_map', fetch: 'EXTRA_LAZY')]
    private Collection $anomalies;

    /**
     * @var ArrayCollection<int, WormholeEntryInterface>
     */
    #[OneToMany(targetEntity: 'WormholeEntry', mappedBy: 'systemMap')]
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
    public function getSx(): int
    {
        return $this->sx;
    }

    #[Override]
    public function setSx(int $sx): StarSystemMapInterface
    {
        $this->sx = $sx;

        return $this;
    }

    #[Override]
    public function getX(): int
    {
        return $this->getSx();
    }

    #[Override]
    public function getSy(): int
    {
        return $this->sy;
    }

    #[Override]
    public function setSy(int $sy): StarSystemMapInterface
    {
        $this->sy = $sy;

        return $this;
    }

    #[Override]
    public function getY(): int
    {
        return $this->getSy();
    }

    #[Override]
    public function getSystemId(): int
    {
        return $this->systems_id;
    }

    #[Override]
    public function getSystem(): StarSystemInterface
    {
        return $this->starSystem;
    }

    #[Override]
    public function setSystem(StarSystemInterface $starSystem): StarSystemMapInterface
    {
        $this->starSystem = $starSystem;

        return $this;
    }

    #[Override]
    public function getFieldId(): int
    {
        return $this->field_id;
    }

    #[Override]
    public function getFieldType(): MapFieldTypeInterface
    {
        return $this->mapFieldType;
    }

    #[Override]
    public function setFieldType(MapFieldTypeInterface $mapFieldType): StarSystemMapInterface
    {
        $this->mapFieldType = $mapFieldType;

        return $this;
    }

    #[Override]
    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function getMapRegion(): ?MapRegionInterface
    {
        return null;
    }

    #[Override]
    public function getAdministratedRegion(): ?MapRegionInterface
    {
        return null;
    }

    #[Override]
    public function getInfluenceArea(): ?StarSystemInterface
    {
        return null;
    }

    #[Override]
    public function getFieldStyle(): string
    {
        return "background-image: url('/assets/map/" . $this->getFieldId() . ".png'); opacity:1;";
    }

    #[Override]
    public function getShips(): Collection
    {
        return $this->ships;
    }

    #[Override]
    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    #[Override]
    public function getAnomalies(): Collection
    {
        return $this->anomalies;
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
                    $type === MapEnum::WORMHOLE_ENTRY_TYPE_OUT);
            }
        );

        return $usableEntries === [] ? null : $usableEntries[array_rand($usableEntries)];
    }

    #[Override]
    public function getSectorString(): string
    {
        return SectorString::getForStarSystemMap($this);
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