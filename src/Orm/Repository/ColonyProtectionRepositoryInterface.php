<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\User;

interface ColonyProtectionRepositoryInterface
{
    public function isProtected(Colony $colony): bool;

    public function deleteByColony(Colony $colony): void;

    public function deleteByQuest(NPCQuest $quest): void;

    public function deleteByQuestOwner(User $user): void;
}
