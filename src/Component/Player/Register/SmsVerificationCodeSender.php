<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\UserInterface;

final class SmsVerificationCodeSender implements SmsVerificationCodeSenderInterface
{
    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function send(UserInterface $player, string $code): void
    {
        $body = <<<EOT
        Dein SMS-Verifikation Code lautet\n\n
        %s
        EOT;

        $mail = new Message();
        $mail->addTo(sprintf('%s@%s', $player->getMobile(), $this->config->get('game.registration.sms_code_verification.email_to_sms_mail_domain')));
        $mail->setFrom($this->config->get('game.registration.sms_code_verification.email_sender_address'));
        $mail->setBody(sprintf($body, $code));

        //try {
        $transport = new Sendmail();
        $transport->send($mail);
        //} catch (RuntimeException $e) {
        return;
        //}
        //TODO try catch wieder rein
    }
}
