<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface TorpedoTypeInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): TorpedoTypeInterface;

    public function getBaseDamage(): int;

    public function getCriticalChance(): int;

    public function setCriticalChance(int $criticalChance): TorpedoTypeInterface;

    public function getHitFactor(): int;

    public function setHitFactor(int $hitFactor): TorpedoTypeInterface;

    public function getHullDamageFactor(): int;

    public function setHullDamageFactor(int $hullDamageFactor): TorpedoTypeInterface;

    public function getShieldDamageFactor(): int;

    public function setShieldDamageFactor(int $shieldDamageFactor): TorpedoTypeInterface;

    public function getVariance(): int;

    public function setVariance(int $variance): TorpedoTypeInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): TorpedoTypeInterface;

    public function getLevel(): int;

    public function setLevel(int $level): TorpedoTypeInterface;

    public function getResearchId(): int;

    public function setResearchId(int $researchId): TorpedoTypeInterface;

    public function getEnergyCost(): int;

    public function setEnergyCost(int $energyCost): TorpedoTypeInterface;

    public function getProductionAmount(): int;

    public function setProductionAmount(int $productionAmount): TorpedoTypeInterface;

    /**
     * @return Collection<int, TorpedoTypeCostInterface>
     */
    public function getProductionCosts(): Collection;

    public function getCommodity(): CommodityInterface;
}
