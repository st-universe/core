<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\TorpedoTypeRepository;

#[Table(name: 'stu_torpedo_types')]
#[Index(name: 'torpedo_type_research_idx', columns: ['research_id'])]
#[Index(name: 'level_idx', columns: ['level'])]
#[Entity(repositoryClass: TorpedoTypeRepository::class)]
class TorpedoType
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $base_damage = 0;

    #[Column(type: 'integer')]
    private int $critical_chance = 0;

    #[Column(type: 'integer')]
    private int $hit_factor = 0;

    #[Column(type: 'integer')]
    private int $hull_damage_factor = 0;

    #[Column(type: 'integer')]
    private int $shield_damage_factor = 0;

    #[Column(type: 'integer')]
    private int $variance = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'integer')]
    private int $level = 0;

    #[Column(type: 'integer')]
    private int $research_id = 0;

    #[Column(type: 'integer')]
    private int $ecost = 0;

    #[Column(type: 'integer')]
    private int $amount = 0;

    /**
     * @var ArrayCollection<int, TorpedoTypeCost>
     */
    #[OneToMany(targetEntity: TorpedoTypeCost::class, mappedBy: 'torpedoType')]
    private Collection $productionCosts;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id')]
    private Commodity $commodity;

    public function __construct()
    {
        $this->productionCosts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): TorpedoType
    {
        $this->name = $name;

        return $this;
    }

    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    public function getCriticalChance(): int
    {
        return $this->critical_chance;
    }

    public function setCriticalChance(int $criticalChance): TorpedoType
    {
        $this->critical_chance = $criticalChance;

        return $this;
    }

    public function getHitFactor(): int
    {
        return $this->hit_factor;
    }

    public function setHitFactor(int $hitFactor): TorpedoType
    {
        $this->hit_factor = $hitFactor;

        return $this;
    }

    public function getHullDamageFactor(): int
    {
        return $this->hull_damage_factor;
    }

    public function setHullDamageFactor(int $hullDamageFactor): TorpedoType
    {
        $this->hull_damage_factor = $hullDamageFactor;

        return $this;
    }

    public function getShieldDamageFactor(): int
    {
        return $this->shield_damage_factor;
    }

    public function setShieldDamageFactor(int $shieldDamageFactor): TorpedoType
    {
        $this->shield_damage_factor = $shieldDamageFactor;

        return $this;
    }

    public function getVariance(): int
    {
        return $this->variance;
    }

    public function setVariance(int $variance): TorpedoType
    {
        $this->variance = $variance;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): TorpedoType
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): TorpedoType
    {
        $this->level = $level;

        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): TorpedoType
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getEnergyCost(): int
    {
        return $this->ecost;
    }

    public function setEnergyCost(int $energyCost): TorpedoType
    {
        $this->ecost = $energyCost;

        return $this;
    }

    public function getProductionAmount(): int
    {
        return $this->amount;
    }

    public function setProductionAmount(int $productionAmount): TorpedoType
    {
        $this->amount = $productionAmount;

        return $this;
    }

    /**
     * @return Collection<int, TorpedoTypeCost>
     */
    public function getProductionCosts(): Collection
    {
        return $this->productionCosts;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }
}
