<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface AstroEntryLibInterface
{
    public function cancelAstroFinalizing(ShipInterface $ship): void;

    public function finish(ShipInterface $ship): void;
}
