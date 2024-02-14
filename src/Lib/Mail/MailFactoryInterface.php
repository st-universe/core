<?php

namespace Stu\Lib\Mail;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;

interface MailFactoryInterface
{
    public function createMessage(): Message;

    public function createSendmail(): Sendmail;
}
