<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\User;

interface QuickPmCrewExperienceInterface
{
    public function awardExperience(
        User $sender,
        int $recipientId,
        int $sourceId,
        int $sourceType,
        int $targetId,
        int $targetType
    ): void;
}
