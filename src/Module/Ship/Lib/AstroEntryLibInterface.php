<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\ShipInterface;

interface AstroEntryLibInterface
{
    public function cancelAstroFinalizing(ShipWrapperInterface $wrapper): void;

    public function finish(ShipWrapperInterface $wrapper): void;

    public function getAstroEntryByShipLocation(ShipInterface $ship, bool $showOverSystem = true): ?AstronomicalEntryInterface;
}
