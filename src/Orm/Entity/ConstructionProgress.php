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
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\ConstructionProgressRepository;

#[Table(name: 'stu_construction_progress')]
#[Entity(repositoryClass: ConstructionProgressRepository::class)]
class ConstructionProgress
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
     * @var ArrayCollection<int, ConstructionProgressModule>
     */
    #[OneToMany(targetEntity: ConstructionProgressModule::class, mappedBy: 'progress', indexBy: 'module_id')]
    #[OrderBy(['module_id' => 'ASC'])]
    private Collection $specialModules;

    #[OneToOne(targetEntity: Station::class)]
    #[JoinColumn(name: 'station_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Station $station;

    public function __construct()
    {
        $this->specialModules = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStation(): Station
    {
        return $this->station;
    }

    public function setStation(Station $station): ConstructionProgress
    {
        $this->station = $station;

        return $this;
    }

    /**
     * @return Collection<int, ConstructionProgressModule>
     */
    public function getSpecialModules(): Collection
    {
        return $this->specialModules;
    }

    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    public function setRemainingTicks(int $remainingTicks): ConstructionProgress
    {
        $this->remaining_ticks = $remainingTicks;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('constructionProgress, stationId: %d', $this->station_id);
    }
}
