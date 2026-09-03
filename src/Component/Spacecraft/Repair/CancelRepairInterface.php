<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

use Stu\Orm\Entity\Spacecraft;

interface CancelRepairInterface
{
    public function cancelRepair(Spacecraft $spacecraft): bool;

    public function cancelRepairWithResult(Spacecraft $spacecraft): CancelRepairResult;
}
