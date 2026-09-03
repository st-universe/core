<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\CrewAssignment;

interface EntityWithCrewAssignmentsInterface
{
    /**
     * @return Collection<int, CrewAssignment>
     */
    public function getCrewAssignments(): Collection;
}
