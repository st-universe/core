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
use Stu\Component\Faction\FactionEnum;
use Stu\Orm\Repository\FactionRepository;

#[Table(name: 'stu_factions')]
#[Entity(repositoryClass: FactionRepository::class)]
class Faction
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'string')]
    private string $darker_color = '';

    #[Column(type: 'boolean')]
    private bool $chooseable = false;

    #[Column(type: 'integer')]
    private int $player_limit = 0;

    #[Column(type: 'integer')]
    private int $start_building_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $start_research_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $start_map_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $close_combat_score = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $positive_effect_primary_commodity_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $positive_effect_secondary_commodity_id = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $welcome_message = '';

    //TODO survivor_rate to escape pods
    #[ManyToOne(targetEntity: Research::class)]
    #[JoinColumn(name: 'start_research_id', referencedColumnName: 'id')]
    private ?Research $start_research = null;

    #[ManyToOne(targetEntity: Map::class)]
    #[JoinColumn(name: 'start_map_id', referencedColumnName: 'id')]
    private ?Map $start_map = null;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'positive_effect_primary_commodity_id', referencedColumnName: 'id')]
    private ?Commodity $primaryEffectCommodity = null;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'positive_effect_secondary_commodity_id', referencedColumnName: 'id')]
    private ?Commodity $secondaryEffectCommodity = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Faction
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Faction
    {
        $this->description = $description;

        return $this;
    }

    public function getDarkerColor(): string
    {
        return $this->darker_color;
    }

    public function setDarkerColor(string $darkerColor): Faction
    {
        $this->darker_color = $darkerColor;

        return $this;
    }

    public function getChooseable(): bool
    {
        return $this->chooseable;
    }

    public function setChooseable(bool $chooseable): Faction
    {
        $this->chooseable = $chooseable;

        return $this;
    }

    public function getPlayerLimit(): int
    {
        return $this->player_limit;
    }

    public function setPlayerLimit(int $playerLimit): Faction
    {
        $this->player_limit = $playerLimit;

        return $this;
    }

    public function getStartBuildingId(): int
    {
        return $this->start_building_id;
    }

    public function setStartBuildingId(int $startBuildingId): Faction
    {
        $this->start_building_id = $startBuildingId;

        return $this;
    }

    public function getStartResearch(): ?Research
    {
        return $this->start_research;
    }

    public function setStartResearch(?Research $start_research): Faction
    {
        $this->start_research = $start_research;
        return $this;
    }

    public function getStartMap(): ?Map
    {
        return $this->start_map;
    }

    public function setStartMap(?Map $start_map): Faction
    {
        $this->start_map = $start_map;
        return $this;
    }

    public function getCloseCombatScore(): ?int
    {
        return $this->close_combat_score;
    }

    public function getPrimaryEffectCommodity(): ?Commodity
    {
        return $this->primaryEffectCommodity;
    }

    public function getSecondaryEffectCommodity(): ?Commodity
    {
        return $this->secondaryEffectCommodity;
    }

    public function getWelcomeMessage(): ?string
    {
        return $this->welcome_message;
    }

    public function setWelcomeMessage(string $welcome_message): Faction
    {
        $this->welcome_message = $welcome_message;

        return $this;
    }
}
