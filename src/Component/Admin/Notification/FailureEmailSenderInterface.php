<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Notification;

interface FailureEmailSenderInterface
{
    public function sendMail(string $subject, string $message): void;
}
