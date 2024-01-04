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
use Stu\Lib\Map\Location;

#[Table(name: 'stu_anomaly')]
#[Index(name: 'anomaly_to_type_idx', columns: ['anomaly_type_id'])]
#[Index(name: 'anomaly_map_idx', columns: ['map_id'])]
#[Index(name: 'anomaly_starsystem_map_idx', columns: ['starsystem_map_id'])]
#[Index(name: 'anomaly_remaining_idx', columns: ['remaining_ticks'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\AnomalyRepository')]
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
    private ?int $map_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $starsystem_map_id = null;

    #[ManyToOne(targetEntity: 'AnomalyType')]
    #[JoinColumn(name: 'anomaly_type_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AnomalyTypeInterface $anomalyType;

    #[ManyToOne(targetEntity: 'Map')]
    #[JoinColumn(name: 'map_id', referencedColumnName: 'id')]
    private ?MapInterface $map = null;

    #[ManyToOne(targetEntity: 'StarSystemMap')]
    #[JoinColumn(name: 'starsystem_map_id', referencedColumnName: 'id')]
    private ?StarSystemMapInterface $starsystem_map = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    public function setRemainingTicks(int $remainingTicks): AnomalyInterface
    {
        $this->remaining_ticks = $remainingTicks;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getRemainingTicks() > 0;
    }

    public function getAnomalyType(): AnomalyTypeInterface
    {
        return $this->anomalyType;
    }

    public function setAnomalyType(AnomalyTypeInterface $anomalyType): AnomalyInterface
    {
        $this->anomalyType = $anomalyType;

        return $this;
    }

    public function getMap(): ?MapInterface
    {
        return $this->map;
    }

    public function setMap(?MapInterface $map): AnomalyInterface
    {
        $this->map = $map;

        return $this;
    }

    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        return $this->starsystem_map;
    }

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): AnomalyInterface
    {
        $this->starsystem_map = $starsystem_map;

        return $this;
    }

    public function getLocation(): Location
    {
        return new Location($this->getMap(), $this->getStarsystemMap());
    }
}
