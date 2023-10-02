<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Message;

use Stu\Module\PlayerSetting\Lib\UserEnum;

final class FightMessage implements FightMessageInterface
{
    /**
     * @var array<string>
     */
    private array $msg = [];

    private int $senderId;

    private ?int $recipientId;

    /**
     * @param array<string>|null $msg
     */
    public function __construct(
        ?int $senderId = null,
        ?int $recipientId = null,
        ?array $msg = null
    ) {
        $this->senderId = $senderId ?? UserEnum::USER_NOONE;
        $this->recipientId = $recipientId;
        if ($msg !== null) {
            $this->msg = $msg;
        }
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
