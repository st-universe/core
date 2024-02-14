<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use JBBCode\Parser;
use Laminas\Mail\Exception\RuntimeException;
use Noodlehaus\ConfigInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PrivateMessageSender implements PrivateMessageSenderInterface
{
    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private MailFactoryInterface $mailFactory;

    private UserRepositoryInterface $userRepository;

    private ConfigInterface $config;

    private LoggerUtilInterface $loggerUtil;

    private Parser $bbcodeParser;

    private StuTime $stuTime;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        UserRepositoryInterface $userRepository,
        MailFactoryInterface $mailFactory,
        ConfigInterface $config,
        Parser $bbcodeParser,
        StuTime $stuTime,
        EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->userRepository = $userRepository;
        $this->mailFactory = $mailFactory;
        $this->config = $config;
        $this->bbcodeParser = $bbcodeParser;
        $this->stuTime = $stuTime;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function send(
        int $senderId,
        int $recipientId,
        string|InformationWrapper $information,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        string $href = null,
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
            $category,
            $text,
            $href,
            !$isRead
        );

        if (
            $category === PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN
            && $recipient->isEmailNotification()
        ) {
            $this->sendEmailNotification($sender->getName(), $text, $recipient);
        }

        if ($senderId != UserEnum::USER_NOONE) {
            $this->entityManager->flush();

            $this->createPrivateMessage(
                $recipient,
                $sender,
                $time,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT,
                $text,
                null,
                false,
                $pm->getId()
            );
        }
    }

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
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
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
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT,
            $text,
            null,
            false
        );
    }

    private function createPrivateMessage(
        UserInterface $sender,
        UserInterface $recipient,
        int $time,
        int $category,
        string $text,
        ?string $href,
        bool $new,
        int $inboxPmId = null
    ): PrivateMessageInterface {
        $folder = $this->privateMessageFolderRepository->getByUserAndSpecial($recipient->getId(), $category);

        if ($folder === null) {
            throw new InvalidArgumentException(sprintf('Folder with user_id %d and category %d does not exist', $recipient->getId(), $category));
        }

        $pm = $this->privateMessageRepository->prototype();
        $pm->setDate($time);
        $pm->setCategory($folder);
        $pm->setText($text);
        $pm->setHref($href);
        $pm->setRecipient($recipient);
        $pm->setSender($sender);
        $pm->setNew($new);
        $pm->setInboxPmId($inboxPmId);

        $this->privateMessageRepository->save($pm);

        return $pm;
    }

    private function sendEmailNotification(string $senderName, string $message, UserInterface $user): void
    {
        $mail = $this->mailFactory->createMessage();

        $mail->addTo($user->getEmail());
        $senderNameAsText = $this->bbcodeParser->parse($senderName)->getAsText();
        $mail->setSubject(sprintf(_('Neue Privatnachricht von Spieler %s'), $senderNameAsText));
        $mail->setFrom($this->config->get('game.email_sender_address'));
        $mail->setBody($message);

        try {
            $transport = $this->mailFactory->createSendmail();
            $transport->send($mail);
        } catch (RuntimeException $e) {
            $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log($e->getMessage());
        }
    }
}
