<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

interface AllianceUserApplicationCheckerInterface
{
    /**
     * Checks if the given user may apply for the given alliance
     */
    public function mayApply(
        User $user,
        Alliance $alliance
    ): bool;
}
