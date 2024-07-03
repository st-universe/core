<?php

namespace Stu\Lib\Mail;

use Override;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;

class MailFactory implements MailFactoryInterface
{
    #[Override]
    public function createMessage(): Message
    {
        return new Message();
    }

    #[Override]
    public function createSendmail(): Sendmail
    {
        return new Sendmail();
    }
}
