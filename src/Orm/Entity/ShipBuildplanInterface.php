<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Shiprump;
use Stu\Lib\ModuleScreen\ModuleSelectWrapper;

interface ShipBuildplanInterface
{
    public function getId(): int;

    public function getRumpId(): int;

    public function setRumpId(int $shipRumpId): ShipBuildplanInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): ShipBuildplanInterface;

    public function getName(): string;

    public function setName(string $name): ShipBuildplanInterface;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): ShipBuildplanInterface;

    public function getSignature(): ?string;

    public function setSignature(?string $signature): ShipBuildplanInterface;

    public function getCrew(): int;

    public function setCrew(int $crew): ShipBuildplanInterface;

    public function getCrewPercentage(): int;

    public function setCrewPercentage(int $crewPercentage): ShipBuildplanInterface;

    public function isDeleteable(): bool;

    public function getRump(): Shiprump;

    public function getModulesByType($type): array;

    public function getModules(): array;

    public function getModule(): ModuleSelectWrapper;
}