<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Crew;

use Stu\Orm\Entity\ShipInterface;

interface LaunchEscapePodsInterface
{
    public function launch(ShipInterface $ship): ?ShipInterface;
}
