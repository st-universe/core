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

#[Table(name: 'stu_factions')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\FactionRepository')]
class Faction implements FactionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
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

    //TODO survivor_rate to escape pods
    #[ManyToOne(targetEntity: 'Research')]
    #[JoinColumn(name: 'start_research_id', referencedColumnName: 'id')]
    private ?ResearchInterface $start_research = null;

    #[ManyToOne(targetEntity: 'Map')]
    #[JoinColumn(name: 'start_map_id', referencedColumnName: 'id')]
    private ?MapInterface $start_map = null;

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\Commodity')]
    #[JoinColumn(name: 'positive_effect_primary_commodity_id', referencedColumnName: 'id')]
    private ?CommodityInterface $primaryEffectCommodity;

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\Commodity')]
    #[JoinColumn(name: 'positive_effect_secondary_commodity_id', referencedColumnName: 'id')]
    private ?CommodityInterface $secondaryEffectCommodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FactionInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): FactionInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDarkerColor(): string
    {
        return $this->darker_color;
    }

    public function setDarkerColor(string $darkerColor): FactionInterface
    {
        $this->darker_color = $darkerColor;

        return $this;
    }

    public function getChooseable(): bool
    {
        return $this->chooseable;
    }

    public function setChooseable(bool $chooseable): FactionInterface
    {
        $this->chooseable = $chooseable;

        return $this;
    }

    public function getPlayerLimit(): int
    {
        return $this->player_limit;
    }

    public function setPlayerLimit(int $playerLimit): FactionInterface
    {
        $this->player_limit = $playerLimit;

        return $this;
    }

    public function getStartBuildingId(): int
    {
        return $this->start_building_id;
    }

    public function setStartBuildingId(int $startBuildingId): FactionInterface
    {
        $this->start_building_id = $startBuildingId;

        return $this;
    }

    public function getStartResearch(): ?ResearchInterface
    {
        return $this->start_research;
    }

    public function setStartResearch(?ResearchInterface $start_research): FactionInterface
    {
        $this->start_research = $start_research;
        return $this;
    }

    public function getStartMap(): ?MapInterface
    {
        return $this->start_map;
    }

    public function setStartMap(?MapInterface $start_map): FactionInterface
    {
        $this->start_map = $start_map;
        return $this;
    }

    public function getCloseCombatScore(): ?int
    {
        return $this->close_combat_score;
    }

    public function getPrimaryEffectCommodity(): ?CommodityInterface
    {
        return $this->primaryEffectCommodity;
    }

    public function getSecondaryEffectCommodity(): ?CommodityInterface
    {
        return $this->secondaryEffectCommodity;
    }
}
