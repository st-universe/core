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
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\MapRegionRepository;

#[Table(name: 'stu_map_regions')]
#[Entity(repositoryClass: MapRegionRepository::class)]
class MapRegion implements MapRegionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = 0;

    #[Column(type: 'string', nullable: true)]
    private ?string $layers = null;

    #[ManyToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    /** @var ArrayCollection<int, AstronomicalEntryInterface> */
    #[OneToMany(targetEntity: AstronomicalEntry::class, mappedBy: 'region', indexBy: 'user_id', fetch: 'EXTRA_LAZY')]
    private Collection $astronomicalEntries;

    public function __construct()
    {
        $this->astronomicalEntries = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): MapRegionInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    #[Override]
    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): MapRegionInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    #[Override]
    public function getAstronomicalEntries(): Collection
    {
        return $this->astronomicalEntries;
    }

    #[Override]
    public function getLayers(): ?string
    {
        return $this->layers;
    }

    #[Override]
    public function setLayers(?string $layers): MapRegionInterface
    {
        $this->layers = $layers;

        return $this;
    }
}
