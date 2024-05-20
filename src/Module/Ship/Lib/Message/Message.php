<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Message;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;

final class Message implements MessageInterface
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
