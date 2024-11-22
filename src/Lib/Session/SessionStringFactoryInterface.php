<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\UserInterface;

interface SessionStringFactoryInterface
{
    public function createSessionString(UserInterface $user): string;
}
