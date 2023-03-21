<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Message;

final class FightMessageCollection implements FightMessageCollectionInterface
{
    /**
     * @var FightMessageInterface[]
     */
    private $messages = [];

    public function add(FightMessageInterface $msg): void
    {
        $this->messages[] = $msg;
    }

    public function addMultiple(array $messages): void
    {
        foreach ($messages as $msg) {
            $this->messages[] = $msg;
        }
    }

    public function getRecipientIds(): array
    {
        $recipientIds = [];

        foreach ($this->messages as $message) {
            $recipientId = $message->getRecipientId();

            if ($recipientId === null) {
                continue;
            }

            if (!in_array($recipientId, $recipientIds)) {
                $recipientIds[] = $recipientId;
            }
        }

        return $recipientIds;
    }

    public function getMessageDump(?int $recipientId = null): array
    {
        $result = [];

        foreach ($this->messages as $message) {
            if (
                $recipientId === null
                || $message->getRecipientId() === null
                || $message->getRecipientId() === $recipientId
            ) {
                $result = array_merge($result, $message->getMessage());
            }
        }

        return $result;
    }
}
