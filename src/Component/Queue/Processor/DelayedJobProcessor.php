<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Processor;

use Stu\Component\Queue\Message\TransformableMessageInterface;

final class DelayedJobProcessor implements DelayedJobProcessorInterface
{
    private array $processors = [];

    public function registerProcessor(
        int $typeId,
        callable $processor
    ): DelayedJobProcessorInterface {
        $this->processors[$typeId] = $processor;

        return $this;
    }

    public function process(TransformableMessageInterface $message): void
    {
       $processor = $this->processors[$message->getId()] ?? null;

       if ($processor === null) {
           throw new \Exception('Unhandled type '.$message->getId());
       }

       $processor($message);
    }
}
