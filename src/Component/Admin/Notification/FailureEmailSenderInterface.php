<?php

namespace Stu\Component\Admin\Notification;

interface FailureEmailSenderInterface
{
    public function sendMail(string $subject, string $message): void;
}
