<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\Location;

final class BorgSphereActivityPolicy
{
    public function __construct(private StuTime $time) {}

    public function canHunt(): bool
    {
        $time = date('H:i', $this->time->time());
        return $time < '00:10' || $time >= '10:00';
    }

    public function canEnter(Location $location): bool
    {
        return $this->canHunt() || $location->getSpacecraftsWithoutCloak()->isEmpty();
    }
}
