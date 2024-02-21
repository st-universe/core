<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\ShipModuleTypeEnum;

interface ShipBuildplanInterface
{
    public function getId(): int;

    public function getRumpId(): int;

    public function setRumpId(int $shipRumpId): ShipBuildplanInterface;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ShipBuildplanInterface;

    public function getName(): string;

    public function setName(string $name): ShipBuildplanInterface;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): ShipBuildplanInterface;

    public function getSignature(): ?string;

    public function setSignature(?string $signature): ShipBuildplanInterface;

    public function getCrew(): int;

    public function setCrew(int $crew): ShipBuildplanInterface;

    public function getShipCount(): int;

    /**
     * @return Collection<int, ShipInterface>
     */
    public function getShiplist(): Collection;

    public function getRump(): ShipRumpInterface;

    public function setRump(ShipRumpInterface $shipRump): ShipBuildplanInterface;

    /**
     * @return array<int, BuildplanModuleInterface>
     */
    public function getModulesByType(ShipModuleTypeEnum $type): array;

    /**
     * @return Collection<int, BuildplanModuleInterface>
     */
    public function getModules(): Collection;
}
