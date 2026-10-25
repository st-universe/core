<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\User;

interface PlotMemberServiceInterface
{
    public function addUserToPlotIfExists(NPCQuest $quest, User $user): void;

    public function notifyMembers(RpgPlot $plot, string $message): void;
}
