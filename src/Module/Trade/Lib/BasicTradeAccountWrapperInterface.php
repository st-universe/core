<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\Station;

interface BasicTradeAccountWrapperInterface
{
    public function getId(): int;

    public function getStation(): Station;

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
