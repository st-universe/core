<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;


final class FightMessage implements FightMessageInterface
{
    private $msg = [];

    private int $senderUserId;

    private ?int $recipientId;

    public function __construct(
        int $senderUserId,
        ?int $recipientId
    ) {
        $this->senderUserId = $senderUserId;
        $this->recipientId = $recipientId;
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

    public function addMessageMerge(array $msg): void
    {
        if (empty($msg)) {
            return;
        }

        $this->msg = array_merge($this->msg, $msg);
    }
}
