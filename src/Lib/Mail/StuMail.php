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

    public function withDefaultSender(): StuMailInterface
    {
        $this->email->from($this
            ->stuConfig
            ->getGameSettings()
            ->getEmailSettings()
            ->getSenderAddress());

        return $this;
    }

    public function setFrom(string $from): StuMailInterface
    {
        $this->email->from($from);

        return $this;
    }

    public function addTo(string $to): StuMailInterface
    {
        $this->email->to($to);

        return $this;
    }

    public function setSubject(string $subject): StuMailInterface
    {
        $this->email->subject($subject);

        return $this;
    }

    public function setBody(string $text): StuMailInterface
    {
        $this->email->text($text);

        return $this;
    }

    public function send(): void
    {
        $this->mailer->send($this->email);
    }
}
