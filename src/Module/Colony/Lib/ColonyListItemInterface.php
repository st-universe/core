<?php

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ColonyStorageInterface;
use Stu\Orm\Entity\PlanetTypeInterface;
use Stu\Orm\Entity\StarSystemInterface;

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

    public function getEpsProduction(): int;

    public function getStorageSum(): int;

    public function getMaxStorage(): int;

    /**
     * @return ColonyStorageInterface[]
     */
    public function getStorage(): Collection;

    public function getPlanetType(): PlanetTypeInterface;

    public function getProductionSum(): int;

    public function getGoodUseView(): array;

    public function isDefended(): bool;

    public function isBlocked(): bool;

    public function getStorageStatusBar(): string;

    public function getEpsStatusBar(): string;
}
