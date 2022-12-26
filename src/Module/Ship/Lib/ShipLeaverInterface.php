<?php

namespace Stu\Module\Ship\Lib;


interface ShipLeaverInterface
{
    public function evacuate(ShipWrapperInterface $wrapper): string;

    public function dumpCrewman(int $shipCrewId): string;
}
