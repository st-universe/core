<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
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

    #[Column(type: 'integer')]
    private int $location_id = 0;

    #[ManyToOne(targetEntity: 'AnomalyType')]
    #[JoinColumn(name: 'anomaly_type_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AnomalyTypeInterface $anomalyType;

    #[ManyToOne(targetEntity: 'Location')]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private LocationInterface $location;

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
    public function getLocation(): LocationInterface
    {
        return $this->location;
    }

    #[Override]
    public function setLocation(LocationInterface $location): AnomalyInterface
    {
        $this->location = $location;

        return $this;
    }
}
