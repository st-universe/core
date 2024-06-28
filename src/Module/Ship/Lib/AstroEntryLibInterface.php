<?php

namespace Stu\Module\Ship\Lib;


interface AstroEntryLibInterface
{
    public function cancelAstroFinalizing(ShipWrapperInterface $wrapper): void;

    public function finish(ShipWrapperInterface $wrapper): void;
}
