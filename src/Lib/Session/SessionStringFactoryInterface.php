<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\User;

interface SessionStringFactoryInterface
{
    public function createSessionString(User $user): string;
}
