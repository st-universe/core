<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;

#[ORM\Entity]
#[ORM\Table(name: 'stu_special_research_definition')]
#[ORM\UniqueConstraint(name: 'special_research_definition_faction_idx', columns: ['research_type', 'faction_id'])]
class SpecialResearchDefinition
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(name: 'research_type', type: 'string', enumType: AwayMissionResearchEnum::class)]
    private AwayMissionResearchEnum $researchType;

    #[ORM\ManyToOne(targetEntity: Faction::class)]
    #[ORM\JoinColumn(name: 'faction_id', referencedColumnName: 'id', nullable: true)]
    private ?Faction $faction = null;

    #[ORM\ManyToOne(targetEntity: Commodity::class)]
    #[ORM\JoinColumn(name: 'data_commodity_id', referencedColumnName: 'id', nullable: false)]
    private Commodity $dataCommodity;

    #[ORM\Column(name: 'data_amount', type: 'integer')]
    private int $dataAmount;

    #[ORM\Column(type: 'integer')]
    private int $duration;

    #[ORM\Column(name: 'failure_chance', type: 'smallint')]
    private int $failureChance;

    /** @var Collection<int, SpecialResearchCost> */
    #[ORM\OneToMany(targetEntity: SpecialResearchCost::class, mappedBy: 'definition')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $costs;

    #[ORM\Column(name: 'failure_damage_percent', type: 'smallint', options: ['default' => 15])]
    private int $failureDamagePercent = 15;

    #[ORM\Column(name: 'failure_worker_min_percent', type: 'smallint', options: ['default' => 25])]
    private int $failureWorkerMinPercent = 25;

    #[ORM\Column(name: 'failure_worker_max_percent', type: 'smallint', options: ['default' => 75])]
    private int $failureWorkerMaxPercent = 75;

    #[ORM\Column(name: 'failure_population_min', type: 'smallint', options: ['default' => 3])]
    private int $failurePopulationMin = 3;

    #[ORM\Column(name: 'failure_population_max', type: 'smallint', options: ['default' => 12])]
    private int $failurePopulationMax = 12;

    public function __construct()
    {
        $this->costs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getResearchType(): AwayMissionResearchEnum
    {
        return $this->researchType;
    }

    public function setResearchType(AwayMissionResearchEnum $researchType): self
    {
        $this->researchType = $researchType;
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

    public function getDataCommodity(): Commodity
    {
        return $this->dataCommodity;
    }

    public function setDataCommodity(Commodity $commodity): self
    {
        $this->dataCommodity = $commodity;
        return $this;
    }

    public function getDataAmount(): int
    {
        return $this->dataAmount;
    }

    public function setDataAmount(int $amount): self
    {
        $this->dataAmount = $amount;
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

    public function setFailureChance(int $chance): self
    {
        $this->failureChance = $chance;
        return $this;
    }

    /** @return Collection<int, SpecialResearchCost> */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    public function getFailureDamagePercent(): int
    {
        return $this->failureDamagePercent;
    }

    public function setFailureDamagePercent(int $value): self
    {
        $this->failureDamagePercent = $value;
        return $this;
    }

    public function getFailureWorkerMinPercent(): int
    {
        return $this->failureWorkerMinPercent;
    }

    public function setFailureWorkerMinPercent(int $value): self
    {
        $this->failureWorkerMinPercent = $value;
        return $this;
    }

    public function getFailureWorkerMaxPercent(): int
    {
        return $this->failureWorkerMaxPercent;
    }

    public function setFailureWorkerMaxPercent(int $value): self
    {
        $this->failureWorkerMaxPercent = $value;
        return $this;
    }

    public function getFailurePopulationMin(): int
    {
        return $this->failurePopulationMin;
    }

    public function setFailurePopulationMin(int $value): self
    {
        $this->failurePopulationMin = $value;
        return $this;
    }

    public function getFailurePopulationMax(): int
    {
        return $this->failurePopulationMax;
    }

    public function setFailurePopulationMax(int $value): self
    {
        $this->failurePopulationMax = $value;
        return $this;
    }

    /** @return array{damage_percent: int, worker_min_percent: int, worker_max_percent: int, population_min: int, population_max: int} */
    public function getFailureSettings(): array
    {
        return [
            'damage_percent' => $this->failureDamagePercent,
            'worker_min_percent' => $this->failureWorkerMinPercent,
            'worker_max_percent' => $this->failureWorkerMaxPercent,
            'population_min' => $this->failurePopulationMin,
            'population_max' => $this->failurePopulationMax
        ];
    }

    private function hasValidFailureSettings(): bool
    {
        return $this->failureDamagePercent >= 0 && $this->failureDamagePercent <= 100
            && $this->failureWorkerMinPercent >= 0 && $this->failureWorkerMaxPercent <= 100
            && $this->failureWorkerMinPercent <= $this->failureWorkerMaxPercent
            && $this->failurePopulationMin >= 0 && $this->failurePopulationMin <= $this->failurePopulationMax;
    }

    public function isValid(): bool
    {
        return $this->hasValidFailureSettings() && $this->dataAmount > 0 && $this->duration > 0
            && $this->failureChance >= 0 && $this->failureChance <= 100
            && !$this->costs->isEmpty()
            && $this->costs->forAll(static fn (int $key, SpecialResearchCost $cost): bool => $cost->getAmount() > 0);
    }
}
