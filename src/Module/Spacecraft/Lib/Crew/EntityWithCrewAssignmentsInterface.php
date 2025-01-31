<?php

namespace Stu\Module\Spacecraft\Lib\Crew;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\CrewAssignmentInterface;

interface EntityWithCrewAssignmentsInterface
{
    /**
     * @return Collection<int, CrewAssignmentInterface>
     */
    public function getCrewAssignments(): Collection;
}
