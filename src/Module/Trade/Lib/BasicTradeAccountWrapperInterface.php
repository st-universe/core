<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\StationInterface;

interface BasicTradeAccountWrapperInterface
{
    public function getId(): int;

    public function getStation(): StationInterface;

    public function getTradePostDescription(): string;

    /**
     * @return array<BasicTradeItemInterface>
     */
    public function getBasicTradeItems(): array;

    public function getLatinumItem(): BasicTradeItem;

    public function getStorageSum(): int;

    public function isOverStorage(): bool;

    public function getStorageCapacity(): int;
}
