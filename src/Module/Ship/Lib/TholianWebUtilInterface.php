<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\TholianWebInterface;

interface TholianWebUtilInterface
{
    public function releaseShipFromWeb(ShipWrapperInterface $wrapper): void;

    public function releaseAllShips(TholianWebInterface $web, ShipWrapperFactoryInterface $shipWrapperFactory): void;

    public function removeWeb(TholianWebInterface $web): void;

    public function releaseWebHelper(ShipWrapperInterface $wrapper): void;

    public function resetWebHelpers(TholianWebInterface $web, ShipWrapperFactoryInterface $shipWrapperFactory, $isFinished = false): void;

    public function updateWebFinishTime(TholianWebInterface $web, ?int $helperModifier = null): void;
}
