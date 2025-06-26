<?php

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\User;

interface SendWelcomeMessageInterface
{
    public function sendWelcomeMessage(User $user): void;
}
