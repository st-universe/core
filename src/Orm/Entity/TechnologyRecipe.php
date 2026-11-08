<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Research\TechnologyRecipeKindEnum;

#[Entity]
#[Table(name: 'stu_technology_recipe')]
class TechnologyRecipe
{
    #[Id]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(name: 'kind', type: 'string', enumType: TechnologyRecipeKindEnum::class)]
    private TechnologyRecipeKindEnum $kind;

    #[Column(name: 'name', type: 'string')]
    private string $name = '';

    #[Column(name: 'research_type', type: 'string', nullable: true, enumType: AwayMissionResearchEnum::class)]
    private ?AwayMissionResearchEnum $researchType = null;

    #[Column(name: 'duration', type: 'integer')]
    private int $duration = 0;

    #[Column(name: 'failure_chance', type: 'integer')]
    private int $failureChance = 0;

    #[Column(name: 'energy_cost', type: 'integer')]
    private int $energyCost = 0;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'output_commodity_id', referencedColumnName: 'id', nullable: true)]
    private ?Commodity $outputCommodity = null;

    #[Column(name: 'output_amount', type: 'integer')]
    private int $outputAmount = 0;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'nanite_commodity_id', referencedColumnName: 'id', nullable: true)]
    private ?Commodity $naniteCommodity = null;

    /** @var array<string, int> */
    #[Column(name: 'settings', type: 'json')]
    private array $settings = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getKind(): TechnologyRecipeKindEnum
    {
        return $this->kind;
    }

    public function setKind(TechnologyRecipeKindEnum $kind): self
    {
        $this->kind = $kind;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getResearchType(): ?AwayMissionResearchEnum
    {
        return $this->researchType;
    }

    public function setResearchType(?AwayMissionResearchEnum $researchType): self
    {
        $this->researchType = $researchType;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getFailureChance(): int
    {
        return $this->failureChance;
    }

    public function setFailureChance(int $failureChance): self
    {
        $this->failureChance = $failureChance;
        return $this;
    }

    public function getEnergyCost(): int
    {
        return $this->energyCost;
    }

    public function setEnergyCost(int $energyCost): self
    {
        $this->energyCost = $energyCost;
        return $this;
    }

    public function getOutputCommodity(): ?Commodity
    {
        return $this->outputCommodity;
    }

    public function setOutputCommodity(?Commodity $outputCommodity): self
    {
        $this->outputCommodity = $outputCommodity;
        return $this;
    }

    public function getOutputAmount(): int
    {
        return $this->outputAmount;
    }

    public function setOutputAmount(int $outputAmount): self
    {
        $this->outputAmount = $outputAmount;
        return $this;
    }

    public function getNaniteCommodity(): ?Commodity
    {
        return $this->naniteCommodity;
    }

    public function setNaniteCommodity(?Commodity $naniteCommodity): self
    {
        $this->naniteCommodity = $naniteCommodity;
        return $this;
    }

    /** @return array<string, int> */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /** @param array<string, int> $settings */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

}
