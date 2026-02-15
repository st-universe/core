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
use Doctrine\ORM\Mapping\Table;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\AnomalyRepository;

#[Table(name: 'stu_anomaly')]
#[Index(name: 'anomaly_to_type_idx', columns: ['anomaly_type_id'])]
#[Index(name: 'anomaly_remaining_idx', columns: ['remaining_ticks'])]
#[Entity(repositoryClass: AnomalyRepository::class)]
#[TruncateOnGameReset]
class Anomaly implements SpacecraftDestroyerInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $remaining_ticks;

    #[Column(type: 'integer')]
    private int $anomaly_type_id;

    #[Column(type: 'text', nullable: true)]
    private ?string $data = null;

    #[ManyToOne(targetEntity: AnomalyType::class)]
    #[JoinColumn(name: 'anomaly_type_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AnomalyType $anomalyType;

    #[ManyToOne(targetEntity: Location::class, inversedBy: 'anomalies')]
    #[JoinColumn(name: 'location_id', nullable: true, referencedColumnName: 'id')]
    private ?Location $location = null;

    #[ManyToOne(targetEntity: Anomaly::class, inversedBy: 'children')]
    #[JoinColumn(name: 'parent_id', nullable: true, referencedColumnName: 'id')]
    private ?Anomaly $parent = null;

    /**
     * @var ArrayCollection<int, Anomaly>
     */
    #[OneToMany(targetEntity: Anomaly::class, mappedBy: 'parent', indexBy: 'locationId')]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    public function setRemainingTicks(int $remainingTicks): Anomaly
    {
        $this->remaining_ticks = $remainingTicks;

        return $this;
    }

    public function changeRemainingTicks(int $amount): Anomaly
    {
        $this->remaining_ticks += $amount;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getRemainingTicks() > 0;
    }

    public function getAnomalyType(): AnomalyType
    {
        return $this->anomalyType;
    }

    public function setAnomalyType(AnomalyType $anomalyType): Anomaly
    {
        $this->anomalyType = $anomalyType;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): Anomaly
    {
        $old = $this->location;
        $this->location = $location;
        $key = $this->anomalyType->getId();

        if ($old !== null) {
            $old->getAnomalies()->remove($key);
        }

        if ($location !== null && !$location->getAnomalies()->containsKey($key)) {
            $location->getAnomalies()->set($key, $this);
        }

        return $this;
    }

    public function getParent(): ?Anomaly
    {
        return $this->parent;
    }

    public function setParent(Anomaly $parent, Location $location): Anomaly
    {
        if ($this->parent === $parent) {
            return $this;
        }

        $this->parent = $parent;
        $parent->getChildren()->set($location->getId(), $this);

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): Anomaly
    {
        $this->data = $data;
        return $this;
    }

    public function getRoot(): Anomaly
    {
        $parent = $this->getParent();

        return $parent === null ? $this : $parent->getRoot();
    }

    /** @return Collection<int, Anomaly> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return !$this->getChildren()->isEmpty();
    }

    #[\Override]
    public function getUserId(): int
    {
        return UserConstants::USER_NOONE;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->getAnomalyType()->getName();
    }
}
