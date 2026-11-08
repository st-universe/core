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

#[Entity]
#[Table(name: 'stu_technology_recipe_cost')]
#[UniqueConstraint(name: 'technology_recipe_cost_idx', columns: ['recipe_id', 'commodity_id'])]
class TechnologyRecipeCost
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: TechnologyRecipe::class)]
    #[JoinColumn(name: 'recipe_id', referencedColumnName: 'id', nullable: false)]
    private TechnologyRecipe $recipe;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', nullable: false)]
    private Commodity $commodity;

    #[Column(name: 'amount', type: 'integer')]
    private int $amount = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
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

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(Commodity $commodity): self
    {
        $this->commodity = $commodity;
        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

}
