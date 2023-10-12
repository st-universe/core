<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;


interface TShipItemInterface
{
    public function getShipId(): int;

    public function getFleetId(): ?int;

    public function getRumpId(): int;

    public function getFormerRumpId(): ?int;

    public function getWarpState(): int;

    public function getCloakState(): int;

    public function getShieldState(): int;

    public function getUplinkState(): int;

    public function isDestroyed(): bool;

    public function getSpacecraftType(): int;

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
