<?php

declare(strict_types=1);

namespace Stu\Lib\Colony;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Component\EntityWithComponentsInterface;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;

interface PlanetFieldHostInterface extends EntityWithComponentsInterface
{
    public const HOST_TYPE_COLONY = 1;
    public const HOST_TYPE_SANDBOX = 2;

    public function getId(): int;

    public function getName(): string;

    public function getUser(): User;

    public function getWorkers(): int;

    public function getPopulation(): int;

    public function getMaxEps(): int;

    public function getMaxStorage(): int;

    public function getColonyClass(): ColonyClass;

    /**
     * @return Collection<int, PlanetField>
     */
    public function getPlanetFields(): Collection;

    public function isColony(): bool;

    public function getHostType(): PlanetFieldHostTypeEnum;

    public function getDefaultViewIdentifier(): string;

    public function isMenuAllowed(ColonyMenuEnum $menu): bool;
}
