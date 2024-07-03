<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Notification;

use Override;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class FailureEmailSender implements FailureEmailSenderInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(private ConfigInterface $config, LoggerUtilFactoryInterface $loggerUtilFactory)
    {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function sendMail(string $subject, string $message): void
    {
        $mail = new Message();
        $mail->addTo($this->config->get('game.admin.email'));
        $mail->setSubject($subject);
        $mail->setFrom($this->config->get('game.email_sender_address'));
        $mail->setBody($message);

        try {
            $transport = new Sendmail();
            $transport->send($mail);
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
