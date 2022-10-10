<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ModuleInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): ModuleInterface;

    public function getLevel(): int;

    public function setLevel(int $level): ModuleInterface;

    public function getUpgradeFactor(): int;

    public function setUpgradeFactor(int $upgradeFactor): ModuleInterface;

    public function getDowngradeFactor(): int;

    public function setDowngradeFactor(int $downgradeFactor): ModuleInterface;

    public function getCrew(): int;

    public function setCrew(int $crew): ModuleInterface;

    public function getType(): int;

    public function setType(int $type): ModuleInterface;

    public function getResearchId(): int;

    public function setResearchId(int $researchId): ModuleInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): ModuleInterface;

    public function getViewable(): bool;

    public function setViewable(bool $viewable): ModuleInterface;

    public function getShipRumpRoleId(): int;

    public function setShipRumpRoleId(int $shipRumpRoleId): ModuleInterface;

    public function getEcost(): int;

    public function setEcost(int $energyCosts): ModuleInterface;

    public function hasSpecial($special_id): bool;

    public function getSpecials(): Collection;

    /**
     * @return ModuleCostInterface[]
     */
    public function getCost(): Collection;

    public function getCostSorted(): array;

    public function getCommodity(): CommodityInterface;

    public function getDescription(): string;
}
