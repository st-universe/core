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
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\AnomalyRepository;

#[Table(name: 'stu_anomaly')]
#[Index(name: 'anomaly_to_type_idx', columns: ['anomaly_type_id'])]
#[Index(name: 'anomaly_remaining_idx', columns: ['remaining_ticks'])]
#[Entity(repositoryClass: AnomalyRepository::class)]
class Anomaly implements AnomalyInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $remaining_ticks;

    #[Column(type: 'integer')]
    private int $anomaly_type_id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $location_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $parent_id = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $data = null;

    #[ManyToOne(targetEntity: AnomalyType::class)]
    #[JoinColumn(name: 'anomaly_type_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AnomalyTypeInterface $anomalyType;

    #[ManyToOne(targetEntity: Location::class)]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private ?LocationInterface $location;

    #[ManyToOne(targetEntity: Anomaly::class)]
    #[JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    private ?AnomalyInterface $parent;

    /**
     * @var ArrayCollection<int, AnomalyInterface>
     */
    #[OneToMany(targetEntity: Anomaly::class, mappedBy: 'parent', indexBy: 'location_id')]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    #[Override]
    public function setRemainingTicks(int $remainingTicks): AnomalyInterface
    {
        $this->remaining_ticks = $remainingTicks;

        return $this;
    }

    #[Override]
    public function changeRemainingTicks(int $amount): AnomalyInterface
    {
        $this->remaining_ticks += $amount;

        return $this;
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->getRemainingTicks() > 0;
    }

    #[Override]
    public function getAnomalyType(): AnomalyTypeInterface
    {
        return $this->anomalyType;
    }

    #[Override]
    public function setAnomalyType(AnomalyTypeInterface $anomalyType): AnomalyInterface
    {
        $this->anomalyType = $anomalyType;

        return $this;
    }

    #[Override]
    public function getLocation(): ?LocationInterface
    {
        return $this->location;
    }

    #[Override]
    public function setLocation(?LocationInterface $location): AnomalyInterface
    {
        $this->location = $location;

        return $this;
    }

    #[Override]
    public function getParent(): ?AnomalyInterface
    {
        return $this->parent;
    }

    #[Override]
    public function setParent(?AnomalyInterface $anomaly): AnomalyInterface
    {
        $this->parent = $anomaly;

        return $this;
    }

    #[Override]
    public function getData(): ?string
    {
        return $this->data;
    }

    #[Override]
    public function setData(string $data): AnomalyInterface
    {
        $this->data = $data;
        return $this;
    }

    #[Override]
    public function getRoot(): AnomalyInterface
    {
        $parent = $this->getParent();

        return $parent === null ? $this : $parent->getRoot();
    }

    #[Override]
    public function getChildren(): Collection
    {
        return $this->children;
    }

    #[Override]
    public function hasChildren(): bool
    {
        return !$this->getChildren()->isEmpty();
    }

    #[Override]
    public function getUserId(): int
    {
        return UserEnum::USER_NOONE;
    }

    #[Override]
    public function getName(): string
    {
        return $this->getAnomalyType()->getName();
    }
}
