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
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Component\Research\TechnologyRecipeKindEnum;

#[Entity]
#[Table(name: 'stu_technology_production')]
#[Index(name: 'technology_production_running_idx', columns: ['user_id', 'status'])]
class TechnologyProduction
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

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'output_commodity_id', referencedColumnName: 'id', nullable: false)]
    private Commodity $outputCommodity;

    #[Column(name: 'output_amount', type: 'integer')]
    private int $outputAmount;

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

    public function getOutputCommodity(): Commodity
    {
        return $this->outputCommodity;
    }

    public function setOutputCommodity(Commodity $outputCommodity): self
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

}
