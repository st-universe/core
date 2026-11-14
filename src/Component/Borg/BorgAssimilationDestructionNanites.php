<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\Spacecraft;

final class BorgAssimilationDestructionNanites
{
    public function __construct(private BorgAssimilationNanites $nanites, private StuTime $time) {}

    public function grantForDestruction(Spacecraft $spacecraft): int
    {
        $entry = $spacecraft->getAssimilation();
        if ($entry === null || (!$entry->isActive() && !$entry->isComplete())) {
            return 0;
        }
        return $this->nanites->grant($entry, $entry->getProgressAt($this->time->time()));
    }
}
