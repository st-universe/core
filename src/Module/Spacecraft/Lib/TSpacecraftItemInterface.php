<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\SpacecraftTypeEnum;

interface TSpacecraftItemInterface
{
    public function getShipId(): int;

    public function getFleetId(): ?int;

    public function getRumpId(): int;

    public function getWarpDriveState(): int;

    public function getTractorWarpState(): int;

    public function isCloaked(): int;

    public function isShielded(): int;

    public function getUplinkState(): int;

    public function getType(): SpacecraftTypeEnum;

    public function getShipName(): string;

    public function getHull(): int;

    public function getMaxHull(): int;

    public function getShield(): int;

    public function getWebId(): ?int;

    public function getWebFinishTime(): ?int;

    public function getUserId(): int;

    public function getUserName(): string;

    public function getRumpCategoryId(): int;

    public function getRumpName(): string;

    public function getRumpRoleId(): ?int;

    public function hasLogbook(): bool;

    public function hasCrew(): bool;
}
