<?php

namespace Stu\Lib\Mail;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Override;

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
