<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\Station;

interface DealAccountWrapperInterface
{
    public function getId(): int;

    public function getStation(): Station;

    public function getStorageSum(): int;

    public function isOverStorage(): bool;

    public function getStorageCapacity(): int;
}
