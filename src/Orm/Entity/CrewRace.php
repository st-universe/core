<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_crew_race')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\CrewRaceRepository')]
class CrewRace implements CrewRaceInterface
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

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\Faction')]
    #[JoinColumn(name: 'faction_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private FactionInterface $faction;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    #[Override]
    public function setFactionId(int $factionId): CrewRaceInterface
    {
        $this->faction_id = $factionId;

        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): CrewRaceInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getChance(): int
    {
        return $this->chance;
    }

    #[Override]
    public function setChance(int $chance): CrewRaceInterface
    {
        $this->chance = $chance;

        return $this;
    }

    #[Override]
    public function getMaleRatio(): int
    {
        return $this->maleratio;
    }

    #[Override]
    public function setMaleRatio(int $maleRatio): CrewRaceInterface
    {
        $this->maleratio = $maleRatio;

        return $this;
    }

    #[Override]
    public function getGfxPath(): string
    {
        return $this->define;
    }

    #[Override]
    public function setGfxPath(string $gfxPath): CrewRaceInterface
    {
        $this->define = $gfxPath;

        return $this;
    }

    #[Override]
    public function getFaction(): FactionInterface
    {
        return $this->faction;
    }

    #[Override]
    public function setFaction(FactionInterface $faction): CrewRaceInterface
    {
        $this->faction = $faction;

        return $this;
    }
}
