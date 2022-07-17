<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JBBCode\Parser;
use Laminas\Mail\Message;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PrivateMessageSender implements PrivateMessageSenderInterface
{
    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private UserRepositoryInterface $userRepository;

    private ConfigInterface $config;

    private LoggerUtilInterface $loggerUtil;

    private Parser $bbcodeParser;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        UserRepositoryInterface $userRepository,
        ConfigInterface $config,
        Parser $bbcodeParser,
        EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->userRepository = $userRepository;
        $this->config = $config;
        $this->bbcodeParser = $bbcodeParser;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function send(
        int $senderId,
        int $recipientId,
        string $text,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        string $href = null
    ): void {
        if ($senderId == $recipientId) {
            return;
        }
        $folder = $this->privateMessageFolderRepository->getByUserAndSpecial((int)$recipientId, (int)$category);
        $recipient = $this->userRepository->find($recipientId);
        $sender = $this->userRepository->find($senderId);

        $pm = $this->privateMessageRepository->prototype();
        $pm->setDate(time());
        $pm->setCategory($folder);
        $pm->setText($text);
        $pm->setHref($href);
        $pm->setRecipient($recipient);
        $pm->setSender($sender);
        $pm->setNew(true);

        if ($recipient->getState() === UserEnum::DELETION_EXECUTED) {
            $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
            $e = new Exception('flaflifu1');
            $this->loggerUtil->log(sprintf('text: %s, trace: %s', $text, $e->getTraceAsString()));
        }

        if ($recipientId === 356) {
            $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
            $e = new Exception('flaflifu2');
            $this->loggerUtil->log(sprintf('text: %s, trace: %s', $text, $e->getTraceAsString()));
        }

        $this->privateMessageRepository->save($pm);

        if ($category === PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN && $recipient->isEmailNotification()) {
            $this->sendEmailNotification($sender->getUserName(), $text, $recipient);
        }

        if ($senderId != GameEnum::USER_NOONE) {

            $this->entityManager->flush();

            $folder = $this->privateMessageFolderRepository->getByUserAndSpecial(
                $senderId,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT
            );

            $newobj = clone ($pm);
            $newobj->setSender($pm->getRecipient());
            $newobj->setRecipient($pm->getSender());
            $newobj->setCategory($folder);
            $newobj->setNew(false);
            $newobj->setInboxPmId($pm->getId());
            $newobj->setHref(null);

            $this->privateMessageRepository->save($newobj);
        }
    }

    private function sendEmailNotification(string $senderName, string $message, UserInterface $user): void
    {
        $mail = new Message();
        $mail->addTo($user->getEmail());
        $senderNameAsText = $this->bbcodeParser->parse($senderName)->getAsText();
        $mail->setSubject(sprintf(_('Neue Privatnachricht von Spieler %s'), $senderNameAsText));
        $mail->setFrom($this->config->get('game.email_sender_address'));
        $mail->setBody($message);

        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (RuntimeException $e) {
            $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log($e->getMessage());
            return;
        }
    }
}
