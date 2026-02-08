<?php

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\CrewAssignment;

interface BoardShipUtilInterface
{
    /**
     * @param array<CrewAssignment> $attackers
     * @param array<CrewAssignment> $defenders
     */
    public function cycleKillRound(
        array &$attackers,
        array &$defenders,
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        MessageCollectionInterface $messages
    ): void;
}
