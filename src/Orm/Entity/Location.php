<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Repository\LocationRepository;

#[Table(name: 'stu_location')]
#[Index(name: 'location_coords_idx', columns: ['layer_id', 'cx', 'cy'])]
#[Index(name: 'location_coords_reverse_idx', columns: ['layer_id', 'cy', 'cx'])]
#[Index(name: 'location_field_type_idx', columns: ['field_id'])]
#[Entity(repositoryClass: LocationRepository::class)]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'discr', type: 'string')]
#[DiscriminatorMap([
    'map' => Map::class,
    'systemMap' => StarSystemMap::class
])]
abstract class Location
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $layer_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cx = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cy = null;

    #[Column(type: 'integer')]
    private int $field_id = 0;

    #[ManyToOne(targetEntity: Layer::class)]
    #[JoinColumn(name: 'layer_id', referencedColumnName: 'id')]
    protected ?Layer $layer;

    #[ManyToOne(targetEntity: MapFieldType::class)]
    #[JoinColumn(name: 'field_id', nullable: false, referencedColumnName: 'id')]
    private MapFieldType $mapFieldType;

    /**
     * @var ArrayCollection<int, Spacecraft>
     */
    #[OneToMany(targetEntity: Spacecraft::class, mappedBy: 'location', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    private Collection $spacecrafts;

    /**
     * @var ArrayCollection<int, Trumfield>
     */
    #[OneToMany(targetEntity: Trumfield::class, mappedBy: 'location', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    private Collection $trumfields;

    /**
     * @var ArrayCollection<int, FlightSignature>
     */
    #[OneToMany(targetEntity: FlightSignature::class, mappedBy: 'location')]
    #[OrderBy(['time' => 'DESC'])]
    private Collection $signatures;

    /**
     * @var ArrayCollection<int, Buoy>
     */
    #[OneToMany(targetEntity: Buoy::class, mappedBy: 'location', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    private Collection $buoys;

    /**
     * @var ArrayCollection<int, Anomaly>
     */
    #[OneToMany(targetEntity: Anomaly::class, mappedBy: 'location', indexBy: 'anomaly_type_id', fetch: 'EXTRA_LAZY')]
    private Collection $anomalies;

    /**
     * @var ArrayCollection<int, LocationMining>
     */
    #[OneToMany(targetEntity: LocationMining::class, mappedBy: 'location')]
    private Collection $locationMinings;

    public function __construct()
    {
        $this->spacecrafts = new ArrayCollection();
        $this->trumfields = new ArrayCollection();
        $this->signatures = new ArrayCollection();
        $this->buoys = new ArrayCollection();
        $this->anomalies = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCx(): ?int
    {
        return $this->cx;
    }

    public function getCy(): ?int
    {
        return $this->cy;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getFieldType(): MapFieldType
    {
        return $this->mapFieldType;
    }

    public function setFieldType(MapFieldType $mapFieldType): Location
    {
        $this->mapFieldType = $mapFieldType;

        return $this;
    }

    /** @return Collection<int, Spacecraft> */
    public function getSpacecrafts(): Collection
    {
        return $this->spacecrafts;
    }

    /** @return Collection<int, Spacecraft> */
    public function getSpacecraftsWithoutCloak(): Collection
    {
        return $this->spacecrafts
            ->filter(fn(Spacecraft $spacecraft): bool => !$spacecraft->isCloaked());
    }

    /** @return Collection<int, Spacecraft> */
    public function getSpacecraftsWithoutVacation(): Collection
    {
        return $this->spacecrafts
            ->filter(fn(Spacecraft $spacecraft): bool => !$spacecraft->getUser()->isVacationRequestOldEnough());
    }

    /** @return Collection<int, Trumfield> */
    public function getTrumfields(): Collection
    {
        return $this->trumfields;
    }

    /**
     * @return Collection<int, Buoy>
     */
    public function getBuoys(): Collection
    {
        return $this->buoys;
    }

    /** @return Collection<int, Anomaly> */
    public function getAnomalies(): Collection
    {
        return $this->anomalies;
    }

    public function addAnomaly(Anomaly $anomaly): void
    {
        $this->anomalies->set($anomaly->getAnomalyType()->getId(), $anomaly);
    }

    public function hasAnomaly(AnomalyTypeEnum $type): bool
    {
        return $this->anomalies->containsKey($type->value);
    }

    public function getAnomaly(AnomalyTypeEnum $type): ?Anomaly
    {
        return $this->anomalies->get($type->value);
    }

    /**
     * @return Collection<int, FlightSignature>
     */
    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    abstract public function getLayer(): ?Layer;

    abstract public function getX(): int;

    abstract public function getY(): int;

    abstract public function getSectorString(): string;

    /**
     * @return Collection<int, WormholeEntry>
     */
    abstract protected function getWormholeEntries(): Collection;

    public function getRandomWormholeEntry(): ?WormholeEntry
    {
        $wormholeEntries = $this->getWormholeEntries();
        if ($wormholeEntries->isEmpty()) {
            return null;
        }

        $usableEntries = $wormholeEntries
            ->filter(fn(WormholeEntry $entry): bool => $entry->isUsable($this))
            ->toArray();

        return $usableEntries === [] ? null : $usableEntries[array_rand($usableEntries)];
    }

    public function isMap(): bool
    {
        return $this instanceof Map;
    }

    public function isOverWormhole(): bool
    {
        return $this->isMap() && $this->getRandomWormholeEntry() !== null;
    }

    /**
     * @return Collection<int, LocationMining>
     */
    public function getLocationMinings(): Collection
    {
        return $this->locationMinings;
    }

    public function isAnomalyForbidden(): bool
    {
        return $this->getFieldType()->hasEffect(FieldTypeEffectEnum::NO_ANOMALIES);
    }
}
