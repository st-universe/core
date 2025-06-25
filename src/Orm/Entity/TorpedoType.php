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
use Override;
use Stu\Orm\Repository\TorpedoTypeRepository;

#[Table(name: 'stu_torpedo_types')]
#[Index(name: 'torpedo_type_research_idx', columns: ['research_id'])]
#[Index(name: 'level_idx', columns: ['level'])]
#[Entity(repositoryClass: TorpedoTypeRepository::class)]
class TorpedoType implements TorpedoTypeInterface
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
     * @var ArrayCollection<int, TorpedoTypeCostInterface>
     */
    #[OneToMany(targetEntity: TorpedoTypeCost::class, mappedBy: 'torpedoType')]
    private Collection $productionCosts;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id')]
    private CommodityInterface $commodity;

    public function __construct()
    {
        $this->productionCosts = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): TorpedoTypeInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    #[Override]
    public function getCriticalChance(): int
    {
        return $this->critical_chance;
    }

    #[Override]
    public function setCriticalChance(int $criticalChance): TorpedoTypeInterface
    {
        $this->critical_chance = $criticalChance;

        return $this;
    }

    #[Override]
    public function getHitFactor(): int
    {
        return $this->hit_factor;
    }

    #[Override]
    public function setHitFactor(int $hitFactor): TorpedoTypeInterface
    {
        $this->hit_factor = $hitFactor;

        return $this;
    }

    #[Override]
    public function getHullDamageFactor(): int
    {
        return $this->hull_damage_factor;
    }

    #[Override]
    public function setHullDamageFactor(int $hullDamageFactor): TorpedoTypeInterface
    {
        $this->hull_damage_factor = $hullDamageFactor;

        return $this;
    }

    #[Override]
    public function getShieldDamageFactor(): int
    {
        return $this->shield_damage_factor;
    }

    #[Override]
    public function setShieldDamageFactor(int $shieldDamageFactor): TorpedoTypeInterface
    {
        $this->shield_damage_factor = $shieldDamageFactor;

        return $this;
    }

    #[Override]
    public function getVariance(): int
    {
        return $this->variance;
    }

    #[Override]
    public function setVariance(int $variance): TorpedoTypeInterface
    {
        $this->variance = $variance;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): TorpedoTypeInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    #[Override]
    public function getLevel(): int
    {
        return $this->level;
    }

    #[Override]
    public function setLevel(int $level): TorpedoTypeInterface
    {
        $this->level = $level;

        return $this;
    }

    #[Override]
    public function getResearchId(): int
    {
        return $this->research_id;
    }

    #[Override]
    public function setResearchId(int $researchId): TorpedoTypeInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    #[Override]
    public function getEnergyCost(): int
    {
        return $this->ecost;
    }

    #[Override]
    public function setEnergyCost(int $energyCost): TorpedoTypeInterface
    {
        $this->ecost = $energyCost;

        return $this;
    }

    #[Override]
    public function getProductionAmount(): int
    {
        return $this->amount;
    }

    #[Override]
    public function setProductionAmount(int $productionAmount): TorpedoTypeInterface
    {
        $this->amount = $productionAmount;

        return $this;
    }

    #[Override]
    public function getProductionCosts(): Collection
    {
        return $this->productionCosts;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}
