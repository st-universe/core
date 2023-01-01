<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;

interface TholianWebUtilInterface
{
    public function releaseShipFromWeb(ShipInterface $ship): void;

    public function releaseAllShips(TholianWebInterface $web): void;

    public function removeWeb(TholianWebInterface $web): void;

    public function releaseWebHelper(ShipWrapperInterface $wrapper): void;

    public function resetWebHelpers(TholianWebInterface $web): void;
}
