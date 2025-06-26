<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Stu\Orm\Entity\User;

interface PlayerDefaultsCreatorInterface
{
    public function createDefault(User $player): void;
}
