<?php

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\UserInterface;

interface SendWelcomeMessageInterface
{
    public function sendWelcomeMessage(UserInterface $user): void;
}
