<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Component\Research\TechnologyRecipeKindEnum;

#[Entity]
#[Table(name: 'stu_technology_research')]
#[Index(name: 'technology_research_running_idx', columns: ['user_id', 'status'])]
class TechnologyResearch
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', nullable: true)]
    private ?Colony $colony = null;

    #[ManyToOne(targetEntity: TechnologyRecipe::class)]
    #[JoinColumn(name: 'recipe_id', referencedColumnName: 'id', nullable: false)]
    private TechnologyRecipe $recipe;

    #[Column(name: 'field_id', type: 'integer')]
    private int $fieldId;

    #[Column(name: 'building_id', type: 'integer')]
    private int $buildingId;

    #[Column(name: 'start_time', type: 'integer')]
    private int $startTime;

    #[Column(name: 'finish_time', type: 'integer')]
    private int $finishTime;

    #[Column(name: 'status', type: 'string', enumType: SpecialResearchStatusEnum::class)]
    private SpecialResearchStatusEnum $status = SpecialResearchStatusEnum::RUNNING;

    #[Column(name: 'kind', type: 'string', enumType: TechnologyRecipeKindEnum::class)]
    private TechnologyRecipeKindEnum $kind;

    #[Column(name: 'research_type', type: 'string', nullable: true, enumType: AwayMissionResearchEnum::class)]
    private ?AwayMissionResearchEnum $researchType = null;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: true)]
    private ?Module $module = null;

    #[Column(name: 'success_chance', type: 'integer')]
    private int $successChance = 0;

    #[Column(name: 'accident_chance', type: 'integer')]
    private int $accidentChance = 0;

    #[Column(name: 'start_improvement_steps', type: 'integer')]
    private int $startImprovementSteps = 0;

    #[Column(name: 'max_improvement_steps', type: 'integer')]
    private int $maxImprovementSteps = 0;

    /** @var array<string, int> */
    #[Column(name: 'failure_settings', type: 'json')]
    private array $failureSettings = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getColony(): ?Colony
    {
        return $this->colony;
    }

    public function setColony(?Colony $colony): self
    {
        $this->colony = $colony;
        return $this;
    }

    public function getRecipe(): TechnologyRecipe
    {
        return $this->recipe;
    }

    public function setRecipe(TechnologyRecipe $recipe): self
    {
        $this->recipe = $recipe;
        return $this;
    }

    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    public function setFieldId(int $fieldId): self
    {
        $this->fieldId = $fieldId;
        return $this;
    }

    public function getBuildingId(): int
    {
        return $this->buildingId;
    }

    public function setBuildingId(int $buildingId): self
    {
        $this->buildingId = $buildingId;
        return $this;
    }

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function setStartTime(int $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getFinishTime(): int
    {
        return $this->finishTime;
    }

    public function setFinishTime(int $finishTime): self
    {
        $this->finishTime = $finishTime;
        return $this;
    }

    public function getStatus(): SpecialResearchStatusEnum
    {
        return $this->status;
    }

    public function setStatus(SpecialResearchStatusEnum $status): self
    {
        $this->status = $status;
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

    public function getResearchType(): ?AwayMissionResearchEnum
    {
        return $this->researchType;
    }

    public function setResearchType(?AwayMissionResearchEnum $researchType): self
    {
        $this->researchType = $researchType;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function getSuccessChance(): int
    {
        return $this->successChance;
    }

    public function setSuccessChance(int $successChance): self
    {
        $this->successChance = $successChance;
        return $this;
    }

    public function getAccidentChance(): int
    {
        return $this->accidentChance;
    }

    public function setAccidentChance(int $accidentChance): self
    {
        $this->accidentChance = $accidentChance;
        return $this;
    }

    public function getStartImprovementSteps(): int
    {
        return $this->startImprovementSteps;
    }

    public function setStartImprovementSteps(int $startImprovementSteps): self
    {
        $this->startImprovementSteps = $startImprovementSteps;
        return $this;
    }

    public function getMaxImprovementSteps(): int
    {
        return $this->maxImprovementSteps;
    }

    public function setMaxImprovementSteps(int $maxImprovementSteps): self
    {
        $this->maxImprovementSteps = $maxImprovementSteps;
        return $this;
    }

    /** @return array<string, int> */
    public function getFailureSettings(): array
    {
        return $this->failureSettings;
    }

    /** @param array<string, int> $failureSettings */
    public function setFailureSettings(array $failureSettings): self
    {
        $this->failureSettings = $failureSettings;
        return $this;
    }

}
