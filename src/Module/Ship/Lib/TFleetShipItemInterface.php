<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

interface TFleetShipItemInterface extends TSpacecraftItemInterface
{
    public function getFleetName(): string;

    public function isDefending(): bool;

    public function isBlocking(): bool;
}
