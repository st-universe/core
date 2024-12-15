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
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\ConstructionProgressRepository;

#[Table(name: 'stu_construction_progress')]
#[Entity(repositoryClass: ConstructionProgressRepository::class)]
class ConstructionProgress implements ConstructionProgressInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $station_id = 0;

    #[Column(type: 'integer')]
    private int $remaining_ticks = 0;

    /**
     * @var ArrayCollection<int, ConstructionProgressModuleInterface>
     */
    #[OneToMany(targetEntity: 'ConstructionProgressModule', mappedBy: 'progress')]
    private Collection $specialModules;

    #[OneToOne(targetEntity: 'Station')]
    #[JoinColumn(name: 'station_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private StationInterface $station;

    public function __construct()
    {
        $this->specialModules = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getStation(): StationInterface
    {
        return $this->station;
    }

    #[Override]
    public function setStation(StationInterface $station): ConstructionProgressInterface
    {
        $this->station = $station;

        return $this;
    }

    #[Override]
    public function getSpecialModules(): Collection
    {
        return $this->specialModules;
    }

    #[Override]
    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    #[Override]
    public function setRemainingTicks(int $remainingTicks): ConstructionProgressInterface
    {
        $this->remaining_ticks = $remainingTicks;

        return $this;
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf('constructionProgress, stationId: %d', $this->station_id);
    }
}
