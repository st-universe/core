<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\ShipInterface;

interface DealAccountWrapperInterface
{
    public function getId(): int;

    public function getShip(): ShipInterface;

    public function getStorageSum(): int;

    public function isOverStorage(): bool;

    public function getStorageCapacity(): int;
}
