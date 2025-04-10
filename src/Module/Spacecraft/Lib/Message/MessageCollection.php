<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Message;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;

final class MessageCollection implements MessageCollectionInterface
{
    /** @var MessageInterface[] */
    private array $messages = [];

    public function __construct(private MessageFactoryInterface $messageFactory) {}

    #[Override]
    public function add(MessageInterface $msg): void
    {
        $this->messages[] = $msg;
    }

    #[Override]
    public function addMessageBy(string|array $text, ?int $recipient = null): MessageInterface
    {
        $message = $this->messageFactory->createMessage(
            UserEnum::USER_NOONE,
            $recipient,
            is_array($text) ? $text : [$text]
        );

        $this->add($message);

        return $message;
    }

    #[Override]
    public function addInformation(?string $information): InformationInterface
    {
        if ($information !== null) {
            $this->addMessageBy($information);
        }

        return $this;
    }

    #[Override]
    public function addInformationf(string $information, ...$args): InformationInterface
    {
        $this->addMessageBy(vsprintf($information, $args));

        return $this;
    }

    #[Override]
    public function getRecipientIds(): array
    {
        $recipientIds = [];

        foreach ($this->messages as $message) {
            if ($message->isEmpty()) {
                continue;
            }

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

    #[Override]
    public function getInformationDump(?int $userId = null): InformationWrapper
    {
        $result = new InformationWrapper();

        foreach ($this->messages as $message) {
            if ($message->isEmpty()) {
                continue;
            }

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

    #[Override]
    public function isEmpty(): bool
    {
        return $this->getInformationDump()->isEmpty();
    }
}
