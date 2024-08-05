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
use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Repository\LocationRepository;

#[Table(name: 'stu_location')]
#[Index(name: 'location_coords_idx', columns: ['layer_id', 'cx', 'cy'])]
#[Index(name: 'location_coords_reverse_idx', columns: ['layer_id', 'cy', 'cx'])]
#[Index(name: 'location_field_type_idx', columns: ['field_id'])]
#[Entity(repositoryClass: LocationRepository::class)]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'discr', type: 'string')]
#[DiscriminatorMap(['map' => Map::class, 'systemMap' => StarSystemMap::class])]
abstract class Location implements LocationInterface
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

    #[ManyToOne(targetEntity: 'Layer')]
    #[JoinColumn(name: 'layer_id', referencedColumnName: 'id')]
    protected ?LayerInterface $layer;

    #[ManyToOne(targetEntity: 'MapFieldType')]
    #[JoinColumn(name: 'field_id', referencedColumnName: 'id')]
    private MapFieldTypeInterface $mapFieldType;

    /**
     * @var ArrayCollection<int, ShipInterface>
     */
    #[OneToMany(targetEntity: 'Ship', mappedBy: 'location', fetch: 'EXTRA_LAZY')]
    private Collection $ships;

    /**
     * @var ArrayCollection<int, FlightSignatureInterface>
     */
    #[OneToMany(targetEntity: 'FlightSignature', mappedBy: 'location')]
    #[OrderBy(['time' => 'DESC'])]
    private Collection $signatures;

    /**
     * @var ArrayCollection<int, BuoyInterface>
     */
    #[OneToMany(targetEntity: 'Buoy', mappedBy: 'location')]
    private Collection $buoys;

    /**
     * @var ArrayCollection<int, AnomalyInterface>
     */
    #[OneToMany(targetEntity: 'Anomaly', mappedBy: 'location', fetch: 'EXTRA_LAZY')]
    private Collection $anomalies;

    /**
     * @var ArrayCollection<int, LocationMiningInterface>
     */
    #[OneToMany(targetEntity: 'LocationMining', mappedBy: 'location')]
    private Collection $locationMinings;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
        $this->signatures = new ArrayCollection();
        $this->buoys = new ArrayCollection();
        $this->anomalies = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getCx(): ?int
    {
        return $this->cx;
    }

    #[Override]
    public function getCy(): ?int
    {
        return $this->cy;
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
    public function setFieldType(MapFieldTypeInterface $mapFieldType): LocationInterface
    {
        $this->mapFieldType = $mapFieldType;

        return $this;
    }

    #[Override]
    public function getShips(): Collection
    {
        return $this->ships;
    }

    #[Override]
    public function getBuoys(): Collection
    {
        return $this->buoys;
    }

    #[Override]
    public function getAnomalies(bool $onlyActive = true): Collection
    {
        return $this->anomalies
            ->filter(fn(AnomalyInterface $anomaly): bool => !$onlyActive || $anomaly->isActive());
    }

    #[Override]
    public function hasAnomaly(AnomalyTypeEnum $type): bool
    {
        return $this->getAnomalies()
            ->exists(fn(int $key, AnomalyInterface $anomaly): bool => $anomaly->getAnomalyType()->getId() === $type->value);
    }

    #[Override]
    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    /**
     * @return Collection<int, WormholeEntryInterface>
     */
    protected abstract function getWormholeEntries(): Collection;

    #[Override]
    public function getRandomWormholeEntry(): ?WormholeEntryInterface
    {
        $wormholeEntries = $this->getWormholeEntries();
        if ($wormholeEntries->isEmpty()) {
            return null;
        }

        $usableEntries = $wormholeEntries
            ->filter(fn(WormholeEntryInterface $entry): bool => $entry->isUsable($this))
            ->toArray();

        return $usableEntries === [] ? null : $usableEntries[array_rand($usableEntries)];
    }

    #[Override]
    public function isMap(): bool
    {
        return $this instanceof MapInterface;
    }

    #[Override]
    public function isOverWormhole(): bool
    {
        return $this->isMap() && $this->getRandomWormholeEntry() !== null;
    }

    #[Override]
    public function getLocationMinings(): Collection
    {
        return $this->locationMinings;
    }
}
