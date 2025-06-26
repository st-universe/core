<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\Spacecraft;

interface AstroEntryLibInterface
{
    public function cancelAstroFinalizing(SpacecraftWrapperInterface $wrapper): void;

    public function finish(ShipWrapperInterface $wrapper): void;

    public function getAstroEntryByShipLocation(Spacecraft $spacecraft, bool $showOverSystem = true): ?AstronomicalEntry;
}
