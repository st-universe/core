<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ColonySandboxInterface
{
    public function getId(): int;

    public function getColony(): ColonyInterface;

    public function getName(): string;

    public function setName(string $name): ColonySandboxInterface;

    public function getWorkers(): int;

    public function setWorkers(int $bev_work): ColonySandboxInterface;

    public function getMaxBev(): int;

    public function setMaxBev(int $bev_max): ColonySandboxInterface;

    public function getMaxEps(): int;

    public function setMaxEps(int $max_eps): ColonySandboxInterface;

    public function getMaxStorage(): int;

    public function setMaxStorage(int $max_storage): ColonySandboxInterface;

    public function getMask(): ?string;

    public function setMask(?string $mask): ColonySandboxInterface;

    /**
     * @return Collection<int, PlanetFieldInterface>
     */
    public function getPlanetFields(): Collection;
}
