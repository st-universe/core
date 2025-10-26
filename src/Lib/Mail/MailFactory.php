<?php

namespace Stu\Lib\Mail;

use Stu\Module\Config\StuConfigInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailFactory implements MailFactoryInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private StuConfigInterface $stuConfig
    ) {}

    #[\Override]
    public function createStuMail(): StuMailInterface
    {
        return new StuMail(
            new Email(),
            $this->mailer,
            $this->stuConfig
        );
    }
}
