<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Message;

use Override;
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

    #[Override]
    public function getSenderId(): int
    {
        return $this->senderId;
    }

    #[Override]
    public function getRecipientId(): ?int
    {
        return $this->recipientId;
    }

    #[Override]
    public function getMessage(): array
    {
        return $this->msg;
    }

    #[Override]
    public function add(?string $msg): void
    {
        if ($msg === null) {
            return;
        }

        $this->msg[] = $msg;
    }

    #[Override]
    public function addInformation(?string $information): InformationInterface
    {
        $this->add($information);

        return $this;
    }

    #[Override]
    public function addInformationf(string $information, ...$args): InformationInterface
    {
        $this->add(vsprintf(
            $information,
            $args
        ));

        return $this;
    }

    #[Override]
    public function addMessageMerge(array $msg): void
    {
        if ($msg === []) {
            return;
        }

        $this->msg = array_merge($this->msg, $msg);
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->msg === [];
    }
}
