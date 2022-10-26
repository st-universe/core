<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface BuildingInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): BuildingInterface;

    public function getStorage(): int;

    public function setStorage(int $storage): BuildingInterface;

    public function getEpsStorage(): int;

    public function setEpsStorage(int $epsStorage): BuildingInterface;

    public function getEpsCost(): int;

    public function setEpsCost(int $epsCost): BuildingInterface;

    public function getEpsProduction(): int;

    public function setEpsProduction(int $epsProduction): BuildingInterface;

    public function getHousing(): int;

    public function setHousing(int $housing): BuildingInterface;

    public function getWorkers(): int;

    public function setWorkers(int $workers): BuildingInterface;

    public function getIntegrity(): int;

    public function setIntegrity(int $integrity): BuildingInterface;

    public function getResearchId(): int;

    public function setResearchId(int $researchId): BuildingInterface;

    public function getView(): bool;

    public function setView(bool $view): BuildingInterface;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): BuildingInterface;

    public function getLimit(): int;

    public function setLimit(int $limit): BuildingInterface;

    public function getLimitColony(): int;

    public function setLimitColony(int $limitColony): BuildingInterface;

    public function getIsActivateable(): bool;

    public function setIsActivateable(bool $isActivateable): BuildingInterface;

    public function getBmCol(): int;

    public function setBmCol(int $buildmenuColumn): BuildingInterface;

    public function getIsBase(): int;

    public function setIsBase($isBase): BuildingInterface;

    public function isActivateable(): bool;

    public function isViewable(): bool;

    public function getBuildingType(): int;

    public function getEpsProductionCss(): string;

    public function hasLimit(): bool;

    public function hasLimitColony(): bool;

    /**
     * @return PlanetFieldTypeBuildingInterface[]
     */
    public function getBuildableFields(): Collection;

    /**
     * @return BuildingCostInterface[]
     */
    public function getCosts(): Collection;

    /**
     * @return BuildingCommodityInterface[]
     */
    public function getCommodities(): Collection;

    /**
     * @return BuildingFunctionInterface[]
     */
    public function getFunctions(): Collection;

    public function postDeactivation(ColonyInterface $colony): void;

    public function postActivation(ColonyInterface $colony): void;

    public function isRemovable(): bool;
}
