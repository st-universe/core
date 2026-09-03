<?php

declare(strict_types=1);

namespace Stu\Lib\Mail;

interface MailFactoryInterface
{
    public function createStuMail(): StuMailInterface;
}
