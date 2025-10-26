<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use InvalidArgumentException;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\General\EntityWithHrefInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PrivateMessageSender implements PrivateMessageSenderInterface
{
    /** @var array<int> */
    public static array $blockedUserIds = [];

    public function __construct(
        private readonly PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private readonly PrivateMessageRepositoryInterface $privateMessageRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EmailNotificationSenderInterface $emailNotificationSender,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly StuTime $stuTime
    ) {}

    #[\Override]
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

        if (in_array($recipientId, self::$blockedUserIds)) {
            return;
        }

        $text = $information instanceof InformationWrapper ? $information->getInformationsAsString() : $information;

        $sender = $this->getSender($senderId);

        $recipient = $this->userRepository->find($recipientId);
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

        if ($sender->isContactable()) {
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

    private function getSender(int $senderId): User
    {
        if (in_array($senderId, self::$blockedUserIds)) {
            return $this->userRepository->getFallbackUser();
        }

        $sender = $this->userRepository->find($senderId);
        if ($sender === null) {
            throw new InvalidArgumentException(sprintf('Sender with id %d does not exist', $senderId));
        }

        return $sender;
    }

    private function getHref(null|string|EntityWithHrefInterface $href): ?string
    {
        return $href instanceof EntityWithHrefInterface
            ? $href->getHref()
            : $href;
    }

    #[\Override]
    public function sendBroadcast(
        User $sender,
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
        User $sender,
        User $recipient,
        int $time,
        PrivateMessageFolderTypeEnum $folderType,
        string $text,
        ?string $href,
        bool $new,
        ?PrivateMessage $inboxPm = null
    ): PrivateMessage {
        $folder = $this->privateMessageFolderRepository->getByUserAndSpecial($recipient->getId(), $folderType);

        if ($folder === null) {
            throw new InvalidArgumentException(sprintf('Folder with user_id %d and category %d does not exist', $recipient->getId(), $folderType->value));
        }

        if (
            $folderType === PrivateMessageFolderTypeEnum::SPECIAL_MAIN
            && $this->userSettingsProvider->isEmailNotification($recipient)
        ) {
            $this->emailNotificationSender->sendNotification($sender->getName(), $text, $recipient);
        }

        $pm = $this->privateMessageRepository->prototype()
            ->setDate($time)
            ->setCategory($folder)
            ->setText($text)
            ->setHref($href)
            ->setRecipient($recipient)
            ->setSender($sender)
            ->setNew($new)
            ->setInboxPm($inboxPm)
            ->setFormerSendUser($sender->getId())
            ->setFormerRecipUser($recipient->getId());

        $this->privateMessageRepository->save($pm);

        return $pm;
    }
}