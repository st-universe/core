<?php

namespace Stu\Lib\Mail;

use Stu\Module\Config\StuConfigInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class StuMail implements StuMailInterface
{
    public function __construct(
        private Email $email,
        private MailerInterface $mailer,
        private StuConfigInterface $stuConfig
    ) {}

    #[\Override]
    public function withDefaultSender(): StuMailInterface
    {
        $this->email->from($this
            ->stuConfig
            ->getGameSettings()
            ->getEmailSettings()
            ->getSenderAddress());

        return $this;
    }

    #[\Override]
    public function setFrom(string $from): StuMailInterface
    {
        $this->email->from($from);

        return $this;
    }

    #[\Override]
    public function addTo(string $to): StuMailInterface
    {
        $this->email->to($to);

        return $this;
    }

    #[\Override]
    public function setSubject(string $subject): StuMailInterface
    {
        $this->email->subject($subject);

        return $this;
    }

    #[\Override]
    public function setBody(string $text): StuMailInterface
    {
        $this->email->text($text);

        return $this;
    }

    #[\Override]
    public function send(): void
    {
        $this->mailer->send($this->email);
    }
}
