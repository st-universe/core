<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\User;

interface SendWelcomeMessageInterface
{
    public function sendWelcomeMessage(User $user): void;
}
