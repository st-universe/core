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
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Component\Crew\CrewTypeEnum;

#[Table(name: 'stu_away_mission_dialogue')]
#[Entity]
#[UniqueConstraint(name: 'away_mission_dialogue_idx', columns: ['mission_id', 'page', 'position', 'faction_id'])]
class AwayMissionDialogue
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: AwayMission::class)]
    #[JoinColumn(name: 'mission_id', referencedColumnName: 'id', nullable: false)]
    private AwayMission $mission;

    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'faction_id', referencedColumnName: 'id', nullable: true)]
    private ?Faction $faction = null;

    #[Column(type: 'integer')]
    private int $page = 1;

    #[Column(type: 'integer')]
    private int $position = 1;

    #[Column(type: 'smallint', nullable: true, enumType: CrewTypeEnum::class)]
    private ?CrewTypeEnum $speaker = null;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $showSurface = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMission(): AwayMission
    {
        return $this->mission;
    }

    public function setMission(AwayMission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function setFaction(?Faction $faction): self
    {
        $this->faction = $faction;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getSpeaker(): ?CrewTypeEnum
    {
        return $this->speaker;
    }

    public function setSpeaker(?CrewTypeEnum $speaker): self
    {
        $this->speaker = $speaker;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function isShowSurface(): bool
    {
        return $this->showSurface;
    }

    public function setShowSurface(bool $showSurface): self
    {
        $this->showSurface = $showSurface;

        return $this;
    }
}
