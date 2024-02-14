<?php

namespace Stu\Lib\Mail;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;

class MailFactory implements MailFactoryInterface
{
    public function createMessage(): Message
    {
        return new Message();
    }

    public function createSendmail(): Sendmail
    {
        return new Sendmail();
    }
}
