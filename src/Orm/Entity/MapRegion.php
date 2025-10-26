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
use Stu\Module\Ship\Lib\EntityWithAstroEntryInterface;
use Stu\Orm\Repository\MapRegionRepository;

#[Table(name: 'stu_map_regions')]
#[Entity(repositoryClass: MapRegionRepository::class)]
class MapRegion implements EntityWithAstroEntryInterface
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
    private ?DatabaseEntry $databaseEntry = null;

    /** @var ArrayCollection<int, AstronomicalEntry> */
    #[OneToMany(
        targetEntity: AstronomicalEntry::class,
        mappedBy: 'region',
        indexBy: 'user_id',
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $astronomicalEntries;

    public function __construct()
    {
        $this->astronomicalEntries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): MapRegion
    {
        $this->description = $description;

        return $this;
    }

    public function getDatabaseEntry(): ?DatabaseEntry
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntry $databaseEntry): MapRegion
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    #[\Override]
    public function getAstronomicalEntries(): Collection
    {
        return $this->astronomicalEntries;
    }

    public function getLayers(): ?string
    {
        return $this->layers;
    }

    public function setLayers(?string $layers): MapRegion
    {
        $this->layers = $layers;

        return $this;
    }
}
