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
     * @var ArrayCollection<int, SpacecraftInterface>
     */
    #[OneToMany(targetEntity: 'Spacecraft', mappedBy: 'location', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    private Collection $spacecrafts;

    /**
     * @var ArrayCollection<int, TrumfieldInterface>
     */
    #[OneToMany(targetEntity: 'Trumfield', mappedBy: 'location', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    private Collection $trumfields;

    /**
     * @var ArrayCollection<int, FlightSignatureInterface>
     */
    #[OneToMany(targetEntity: 'FlightSignature', mappedBy: 'location')]
    #[OrderBy(['time' => 'DESC'])]
    private Collection $signatures;

    /**
     * @var ArrayCollection<int, BuoyInterface>
     */
    #[OneToMany(targetEntity: 'Buoy', mappedBy: 'location', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    private Collection $buoys;

    /**
     * @var ArrayCollection<int, AnomalyInterface>
     */
    #[OneToMany(targetEntity: 'Anomaly', mappedBy: 'location', indexBy: 'anomaly_type_id', fetch: 'EXTRA_LAZY')]
    private Collection $anomalies;

    /**
     * @var ArrayCollection<int, LocationMiningInterface>
     */
    #[OneToMany(targetEntity: 'LocationMining', mappedBy: 'location')]
    private Collection $locationMinings;

    public function __construct()
    {
        $this->spacecrafts = new ArrayCollection();
        $this->trumfields = new ArrayCollection();
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
    public function getSpacecrafts(): Collection
    {
        return $this->spacecrafts;
    }

    #[Override]
    public function getSpacecraftsWithoutVacation(): Collection
    {
        return $this->spacecrafts
            ->filter(fn(SpacecraftInterface $spacecraft): bool => !$spacecraft->getUser()->isVacationRequestOldEnough());
    }

    #[Override]
    public function getTrumfields(): Collection
    {
        return $this->trumfields;
    }

    #[Override]
    public function getBuoys(): Collection
    {
        return $this->buoys;
    }

    #[Override]
    public function getAnomalies(): Collection
    {
        return $this->anomalies;
    }

    public function addAnomaly(AnomalyInterface $anomaly): void
    {
        $this->anomalies->set($anomaly->getAnomalyType()->getId(), $anomaly);
    }

    #[Override]
    public function hasAnomaly(AnomalyTypeEnum $type): bool
    {
        return $this->anomalies->containsKey($type->value);
    }

    #[Override]
    public function getAnomaly(AnomalyTypeEnum $type): ?AnomalyInterface
    {
        return $this->anomalies->get($type->value);
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

    #[Override]
    public function isAnomalyForbidden(): bool
    {
        return $this->getFieldType()->hasEffect(FieldTypeEffectEnum::NO_ANOMALIES);
    }
}
