<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use InvalidArgumentException;
use Override;
use Stu\Lib\General\EntityWithHrefInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuTime;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PrivateMessageSender implements PrivateMessageSenderInterface
{
    public function __construct(
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private PrivateMessageRepositoryInterface $privateMessageRepository,
        private UserRepositoryInterface $userRepository,
        private EmailNotificationSenderInterface $emailNotificationSender,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function send(
        int $senderId,
        int $recipientId,
        string|InformationWrapper $information,
        PrivateMessageFolderTypeEnum $folderType = PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
        null|string|EntityWithHrefInterface $href = null,
        bool $isRead = false
    ): void {
        if ($senderId === $recipientId) {
            return;
        }

        if (
            $information instanceof InformationWrapper
            && $information->isEmpty()
        ) {
            return;
        }

        $text = $information instanceof InformationWrapper ? $information->getInformationsAsString() : $information;

        $recipient = $this->userRepository->find($recipientId);
        $sender = $this->userRepository->find($senderId);

        if ($sender === null) {
            throw new InvalidArgumentException(sprintf('Sender with id %d does not exist', $senderId));
        }
        if ($recipient === null) {
            throw new InvalidArgumentException(sprintf('Recipient with id %d does not exist', $recipientId));
        }

        $time = $this->stuTime->time();

        $pm = $this->createPrivateMessage(
            $sender,
            $recipient,
            $time,
            $folderType,
            $text,
            $this->getHref($href),
            !$isRead
        );

        if ($senderId != UserEnum::USER_NOONE) {
            $this->createPrivateMessage(
                $recipient,
                $sender,
                $time,
                PrivateMessageFolderTypeEnum::SPECIAL_PMOUT,
                $text,
                null,
                false,
                $pm
            );
        }
    }

    private function getHref(null|string|EntityWithHrefInterface $href): ?string
    {
        if ($href instanceof EntityWithHrefInterface) {
            return $href->getHref();
        }

        return $href !== null ? $href : null;
    }

    #[Override]
    public function sendBroadcast(
        UserInterface $sender,
        array $recipients,
        string $text
    ): void {
        if ($recipients === []) {
            return;
        }

        $time = $this->stuTime->time();

        //broadcast pm to every recipient
        foreach ($recipients as $recipient) {
            $this->createPrivateMessage(
                $sender,
                $recipient,
                $time,
                PrivateMessageFolderTypeEnum::SPECIAL_MAIN,
                $text,
                null,
                true
            );
        }

        //single item to outbox
        $this->createPrivateMessage(
            $this->userRepository->getFallbackUser(),
            $sender,
            $time,
            PrivateMessageFolderTypeEnum::SPECIAL_PMOUT,
            $text,
            null,
            false
        );
    }

    private function createPrivateMessage(
        UserInterface $sender,
        UserInterface $recipient,
        int $time,
        PrivateMessageFolderTypeEnum $folderType,
        string $text,
        ?string $href,
        bool $new,
        ?PrivateMessageInterface $inboxPm = null
    ): PrivateMessageInterface {
        $folder = $this->privateMessageFolderRepository->getByUserAndSpecial($recipient->getId(), $folderType);

        if ($folder === null) {
            throw new InvalidArgumentException(sprintf('Folder with user_id %d and category %d does not exist', $recipient->getId(), $folderType->value));
        }

        if (
            $folderType === PrivateMessageFolderTypeEnum::SPECIAL_MAIN
            && $recipient->isEmailNotification()
        ) {
            $this->emailNotificationSender->sendNotification($sender->getName(), $text, $recipient);
        }

        $pm = $this->privateMessageRepository->prototype();
        $pm->setDate($time);
        $pm->setCategory($folder);
        $pm->setText($text);
        $pm->setHref($href);
        $pm->setRecipient($recipient);
        $pm->setSender($sender);
        $pm->setNew($new);
        if ($inboxPm !== null) {
            $pm->setInboxPm($inboxPm);
        }

        $this->privateMessageRepository->save($pm);

        return $pm;
    }
}
