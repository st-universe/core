<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\SpacecraftInterface;

interface SpacecraftRemoverInterface
{
    /**
     * Actually removes the spacecraft entity including all references
     */
    public function remove(SpacecraftInterface $spacecraft, ?bool $truncateCrew = false): void;
}
