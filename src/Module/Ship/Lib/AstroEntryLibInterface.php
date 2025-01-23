<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface AstroEntryLibInterface
{
    public function cancelAstroFinalizing(SpacecraftWrapperInterface $wrapper): void;

    public function finish(ShipWrapperInterface $wrapper): void;

    public function getAstroEntryByShipLocation(SpacecraftInterface $spacecraft, bool $showOverSystem = true): ?AstronomicalEntryInterface;
}
