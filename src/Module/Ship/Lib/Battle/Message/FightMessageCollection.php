<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Message;

use Stu\Lib\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;

final class FightMessageCollection implements FightMessageCollectionInterface
{
    /**
     * @var FightMessageInterface[]
     */
    private array $messages = [];

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

            if ($recipientId === null || $recipientId === UserEnum::USER_NOONE) {
                continue;
            }

            if (!in_array($recipientId, $recipientIds)) {
                $recipientIds[] = $recipientId;
            }
        }

        return $recipientIds;
    }

    public function getInformationDump(?int $userId = null): InformationWrapper
    {
        $result = new InformationWrapper();

        foreach ($this->messages as $message) {
            if (
                $userId === null
                || $message->getSenderId() === $userId
                || $message->getRecipientId() === null
                || $message->getRecipientId() === $userId
            ) {
                $result->addInformationArray($message->getMessage());
            }
        }

        return $result;
    }
}
