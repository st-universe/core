<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler;

use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;

abstract class AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private array $msgInternal = [];

    public function clearMessages(): void
    {
        $this->msgInternal = [];
    }

    public function addMessageInternal(string $msg): void
    {
        $this->msgInternal[] = $msg;
    }

    public function addMessagesInternal(array $msg): void
    {
        foreach ($msg as $message) {
            $this->addMessageInternal($message);
        }
    }

    public function getInternalMsg(): array
    {
        return $this->msgInternal;
    }
}
