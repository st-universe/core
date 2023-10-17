<?php

namespace Stu\Module\Ship\Lib\Crew;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipCrewInterface;

interface ShipLeaverInterface
{
    public function evacuate(ShipWrapperInterface $wrapper): string;

    public function shutdown(ShipWrapperInterface $wrapper): void;

    public function dumpCrewman(ShipCrewInterface $shipCrew, string $message): string;
}
