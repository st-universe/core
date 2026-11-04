<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stu_special_research_cost')]
#[ORM\UniqueConstraint(name: 'special_research_cost_commodity_idx', columns: ['definition_id', 'commodity_id'])]
class SpecialResearchCost
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: SpecialResearchDefinition::class, inversedBy: 'costs')]
    #[ORM\JoinColumn(name: 'definition_id', referencedColumnName: 'id', nullable: false)]
    private SpecialResearchDefinition $definition;

    #[ORM\ManyToOne(targetEntity: Commodity::class)]
    #[ORM\JoinColumn(name: 'commodity_id', referencedColumnName: 'id', nullable: false)]
    private Commodity $commodity;

    #[ORM\Column(type: 'integer')]
    private int $amount;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDefinition(): SpecialResearchDefinition
    {
        return $this->definition;
    }

    public function setDefinition(SpecialResearchDefinition $definition): self
    {
        $this->definition = $definition;
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
