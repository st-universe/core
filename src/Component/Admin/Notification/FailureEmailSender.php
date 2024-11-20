<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Notification;

use Noodlehaus\ConfigInterface;
use Override;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class FailureEmailSender implements FailureEmailSenderInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ConfigInterface $config,
        private MailFactoryInterface $mailFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function sendMail(string $subject, string $message): void
    {
        $mail = $this->mailFactory->createStuMail()
            ->withDefaultSender()
            ->addTo($this->config->get('game.admin.email'))
            ->setSubject($subject)
            ->setBody($message);

        try {
            $mail->send();
        } catch (RuntimeException) {
            $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log(sprintf(
                "Error while sending failure E-Mail to admin! Subject: %s, Message: %s",
                $subject,
                $message
            ));
        }
    }
}
