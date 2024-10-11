<?php

namespace Stu\Lib\Mail;

interface StuMailInterface
{
    public function withDefaultSender(): StuMailInterface;

    public function setFrom(string $from): StuMailInterface;

    public function addTo(string $to): StuMailInterface;

    public function setSubject(string $to): StuMailInterface;

    public function setBody(string $text): StuMailInterface;

    public function send(): void;
}
