<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\Spacecraft;

interface SpacecraftRemoverInterface
{
    /**
     * Actually removes the spacecraft entity including all references
     */
    public function remove(Spacecraft $spacecraft, ?bool $truncateCrew = false): void;
}
