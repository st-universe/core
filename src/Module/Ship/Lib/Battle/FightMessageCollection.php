<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

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

    public function getRecipientIds(): array
    {
        $recipientIds = [];

        foreach ($this->messages as $message) {
            $recipientId = $message->getRecipientId();

            if ($recipientId === null) {
                continue;
            }

            if (!array_key_exists($recipientId, $recipientIds)) {
                $recipientIds[$recipientId] = $recipientId;
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
