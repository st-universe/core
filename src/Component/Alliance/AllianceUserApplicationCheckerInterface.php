<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;

interface AllianceUserApplicationCheckerInterface
{
    /**
     * Checks if the given user may apply for the given alliance
     */
    public function mayApply(
        UserInterface $user,
        AllianceInterface $alliance
    ): bool;
}
