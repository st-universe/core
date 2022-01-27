<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Notification;

use Noodlehaus\ConfigInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Transport\Sendmail;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class FailureEmailSender implements FailureEmailSenderInterface
{

    private ConfigInterface $config;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(ConfigInterface $config, LoggerUtilFactoryInterface $loggerUtilFactory)
    {
        $this->config = $config;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

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
        } catch (RuntimeException $e) {
            $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log(sprintf(
                "Error while sending failure E-Mail to admin! Subject: %s, Message: %s",
                $subject,
                $message
            ));
            return;
        }
    }
}
