<?php

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StorageInterface;

interface ColonyListItemInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getSystem(): StarSystemInterface;

    public function getSX(): int;

    public function getSY(): int;

    public function getSignatureCount(): int;

    public function getPopulation(): int;

    public function getHousing(): int;

    public function getImmigration(): int;

    public function getEps(): int;

    public function getMaxEps(): int;

    public function getEnergyProduction(): int;

    public function getStorageSum(): int;

    public function getMaxStorage(): int;

    /**
     * @return Collection<int, StorageInterface>
     */
    public function getStorage(): Collection;

    public function getColonyClass(): ColonyClassInterface;

    public function getProductionSum(): int;

    /**
     * @return array<int, array{turnsleft:int, commodity:CommodityInterface}>
     */
    public function getCommodityUseView(): array;

    public function isDefended(): bool;

    public function isBlocked(): bool;

    public function getCrewAssignmentAmount(): int;

    public function getCrewTrainingAmount(): int;

    public function getCrewLimit(): int;

    public function getCrewLimitStyle(): string;

    public function getStorageStatusBar(): string;

    public function getEpsStatusBar(): string;
}
