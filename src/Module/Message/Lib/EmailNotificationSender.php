<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use JBBCode\Parser;
use Override;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\UserInterface;

final class EmailNotificationSender implements EmailNotificationSenderInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private MailFactoryInterface $mailFactory,
        private Parser $bbcodeParser,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function sendNotification(string $senderName, string $message, UserInterface $user): void
    {
        $mail = $this->mailFactory->createStuMail()
            ->withDefaultSender()
            ->addTo($user->getEmail())
            ->setSubject(sprintf(
                'Neue Privatnachricht von Spieler %s',
                $this->bbcodeParser->parse($senderName)->getAsText()
            ))
            ->setBody($message);

        try {
            $mail->send();
        } catch (RuntimeException $e) {
            $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log($e->getMessage());
        }
    }
}
