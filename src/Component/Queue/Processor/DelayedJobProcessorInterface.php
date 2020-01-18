<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Processor;

use Stu\Component\Queue\Message\TransformableMessageInterface;

interface DelayedJobProcessorInterface
{
    public function registerProcessor(int $typeId, callable $processor): DelayedJobProcessorInterface;

    public function process(TransformableMessageInterface $message): void;
}
