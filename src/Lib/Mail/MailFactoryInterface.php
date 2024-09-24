<?php

namespace Stu\Lib\Mail;

interface MailFactoryInterface
{
    public function createStuMail(): StuMailInterface;
}
