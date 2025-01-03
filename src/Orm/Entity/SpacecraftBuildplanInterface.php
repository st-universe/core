<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;

interface SpacecraftBuildplanInterface
{
    public function getId(): int;

    public function getRumpId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): SpacecraftBuildplanInterface;

    public function getName(): string;

    public function setName(string $name): SpacecraftBuildplanInterface;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): SpacecraftBuildplanInterface;

    public function getSignature(): ?string;

    public function setSignature(?string $signature): SpacecraftBuildplanInterface;

    public function getCrew(): int;

    public function setCrew(int $crew): SpacecraftBuildplanInterface;

    public function getSpacecraftCount(): int;

    /**
     * @return Collection<int, SpacecraftInterface>
     */
    public function getSpacecraftList(): Collection;

    public function getRump(): SpacecraftRumpInterface;

    public function setRump(SpacecraftRumpInterface $rump): SpacecraftBuildplanInterface;

    /**
     * @return Collection<int, ModuleInterface>
     */
    public function getModulesByType(SpacecraftModuleTypeEnum $type): Collection;

    /**
     * @return Collection<int, BuildplanModuleInterface>
     */
    public function getModules(): Collection;

    /**
     * @return Collection<int, BuildplanModuleInterface>
     */
    public function getModulesOrdered(): Collection;
}
