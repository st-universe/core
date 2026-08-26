<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\CrewRaceRepository;

#[Table(name: 'stu_crew_race')]
#[Entity(repositoryClass: CrewRaceRepository::class)]
class CrewRace
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $faction_id = 0;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'smallint')]
    private int $chance = 0;

    #[Column(type: 'smallint')]
    private int $maleratio = 0;

    #[Column(type: 'string')]
    private string $define = '';

    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'faction_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Faction $faction;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $factionId): CrewRace
    {
        $this->faction_id = $factionId;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): CrewRace
    {
        $this->description = $description;

        return $this;
    }

    public function getChance(): int
    {
        return $this->chance;
    }

    public function setChance(int $chance): CrewRace
    {
        $this->chance = $chance;

        return $this;
    }

    public function getMaleRatio(): int
    {
        return $this->maleratio;
    }

    public function setMaleRatio(int $maleRatio): CrewRace
    {
        $this->maleratio = $maleRatio;

        return $this;
    }

    public function getGfxPath(): string
    {
        return $this->define;
    }

    public function setGfxPath(string $gfxPath): CrewRace
    {
        $this->define = $gfxPath;

        return $this;
    }

    public function getFaction(): Faction
    {
        return $this->faction;
    }

    public function setFaction(Faction $faction): CrewRace
    {
        $this->faction = $faction;

        return $this;
    }
}
