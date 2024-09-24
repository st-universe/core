<?php

namespace Stu\Lib\Mail;

use Override;
use Symfony\Component\Mailer\Mailer;

class MailFactory implements MailFactoryInterface
{
    public function __construct(private Mailer $mailer) {}

    #[Override]
    public function createStuMail(): StuMailInterface
    {
        return new StuMail($this->mailer);
    }
}
