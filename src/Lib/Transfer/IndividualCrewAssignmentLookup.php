<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Closure;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

final class IndividualCrewAssignmentLookup
{
    public function forEachOwnAssignment(
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        User $user,
        Closure $consumer
    ): void {
        foreach ([$source, $target] as $side => $wrapper) {
            $entity = $wrapper->get();
            foreach ($entity->getCrewAssignments() as $assignment) {
                $crew = $assignment->getCrew();
                if ($crew->getUserId() !== $user->getId()) {
                    continue;
                }

                $slot = $entity instanceof Spacecraft
                    ? ($assignment->getSlot() ?? CrewTypeEnum::CREWMAN)
                    : CrewTypeEnum::CREWMAN;

                $consumer(new IndividualCrewAssignmentLocation($assignment, $side, $slot));
            }
        }
    }
}
