<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Message;

use Stu\Lib\Information\InformationInterface;

final class Message implements MessageInterface
{
    /**
     * @param array<string> $msg
     */
    public function __construct(
        private int $senderId,
        private ?int $recipientId,
        private array $msg = []
    ) {
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function getRecipientId(): ?int
    {
        return $this->recipientId;
    }

    public function getMessage(): array
    {
        return $this->msg;
    }

    public function add(?string $msg): void
    {
        if ($msg === null) {
            return;
        }

        $this->msg[] = $msg;
    }

    public function addInformation(?string $information): InformationInterface
    {
        $this->add($information);

        return $this;
    }

    public function addInformationf(string $information, ...$args): InformationInterface
    {
        $this->add(vsprintf(
            $information,
            $args
        ));

        return $this;
    }

    public function addMessageMerge(array $msg): void
    {
        if ($msg === []) {
            return;
        }

        $this->msg = array_merge($this->msg, $msg);
    }

    public function isEmpty(): bool
    {
        return empty($this->getMessage());
    }
}
