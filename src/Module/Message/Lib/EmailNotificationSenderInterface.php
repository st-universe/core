<?php

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\User;

interface EmailNotificationSenderInterface
{
    public function sendNotification(string $senderName, string $message, User $user): void;
}
