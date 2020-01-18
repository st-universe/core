<?php

namespace Stu\Component\Queue\Consumer;

interface DelayedJobConsumerInterface
{
    public function consume(): void;
}
