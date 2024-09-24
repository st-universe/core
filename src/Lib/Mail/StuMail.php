<?php

namespace Stu\Lib\Mail;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class StuMail implements StuMailInterface
{
    private Email $email;

    public function __construct(private Mailer $mailer)
    {
        $this->email = new Email();
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
