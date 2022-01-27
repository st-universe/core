<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use JBBCode\Parser;
use Laminas\Mail\Message;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
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

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        UserRepositoryInterface $userRepository,
        ConfigInterface $config,
        Parser $bbcodeParser,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->userRepository = $userRepository;
        $this->config = $config;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->bbcodeParser = $bbcodeParser;
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

        $this->privateMessageRepository->save($pm);

        if ($category === PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN && $recipient->isEmailNotification()) {
            $this->sendEmailNotification($sender->getUserName(), $text, $recipient);
        }

        if ($senderId != GameEnum::USER_NOONE) {

            $folder = $this->privateMessageFolderRepository->getByUserAndSpecial(
                $senderId,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT
            );

            $newobj = clone ($pm);
            $newobj->setSender($pm->getRecipient());
            $newobj->setRecipient($pm->getSender());
            $newobj->setCategory($folder);
            $newobj->setNew(false);
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
