<?php

namespace Stu\Module\Tal;

use Stu\Orm\Entity\ShipRumpInterface;

interface OrbitShipItemInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getUserName(): string;

    public function isDestroyed(): bool;

    public function getRump(): ShipRumpInterface;

    public function getRumpId(): int;

    public function getFormerRumpId(): int;

    public function getRumpName(): string;

    public function getHull(): int;

    public function getShield(): int;

    public function getEps(): int;

    public function ownedByUser(): bool;

    public function getHullStatusBar(): string;

    public function getShieldStatusBar(): string;

    public function getEpsStatusBar(): string;

    public function getCloakState(): bool;
}
