<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler;

abstract class AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    /** @var list<string> */
    private array $msgInternal = [];

    public function clearMessages(): void
    {
        $this->msgInternal = [];
    }

    public function addMessageInternal(string $msg): void
    {
        $this->msgInternal[] = $msg;
    }

    /**
     * @param array<string> $msg
     */
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
