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

    public function getHull(): int;

    public function getShield(): int;

    public function getEps(): int;

    public function getHullStatusBar(): string;

    public function getShieldStatusBar(): string;

    public function getEpsStatusBar(): string;
}
