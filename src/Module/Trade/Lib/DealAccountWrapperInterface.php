<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\StationInterface;

interface DealAccountWrapperInterface
{
    public function getId(): int;

    public function getStation(): StationInterface;

    public function getStorageSum(): int;

    public function isOverStorage(): bool;

    public function getStorageCapacity(): int;
}
