<?php

namespace Stu\Orm\Entity;

use Stu\Lib\Colony\PlanetFieldHostInterface;

interface ColonySandboxInterface extends PlanetFieldHostInterface
{
    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonySandboxInterface;

    public function getName(): string;

    public function setName(string $name): ColonySandboxInterface;

    public function setWorkers(int $bev_work): ColonySandboxInterface;

    public function getMaxBev(): int;

    public function setMaxBev(int $bev_max): ColonySandboxInterface;

    public function setMaxEps(int $max_eps): ColonySandboxInterface;

    public function getMaxStorage(): int;

    public function setMaxStorage(int $max_storage): ColonySandboxInterface;

    public function getMask(): ?string;

    public function setMask(?string $mask): ColonySandboxInterface;

    public function getTwilightZone(): int;

    public function getSurfaceWidth(): int;
}
