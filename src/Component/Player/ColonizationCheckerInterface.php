<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;

interface ColonizationCheckerInterface
{
    public function canColonize(User $user, Colony $colony): bool;
}
