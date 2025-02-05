<?php

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\UserInterface;

interface EmailNotificationSenderInterface
{
    public function sendNotification(string $senderName, string $message, UserInterface $user): void;
}
