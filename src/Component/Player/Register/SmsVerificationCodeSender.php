<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Orm\Entity\UserInterface;

final class SmsVerificationCodeSender implements SmsVerificationCodeSenderInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    #[Override]
    public function send(UserInterface $player, string $code): void
    {
        $body = <<<EOT
            Dein Aktivierungscode für Star Trek Universe lautet:\n\n
            %s\n\n
            Viel Spaß!
            EOT;

        $mail = new Message();
        $mail->addTo(sprintf('%s@%s', $player->getMobile(), $this->config->get('game.registration.sms_code_verification.email_to_sms_mail_domain')));
        $mail->setFrom($this->config->get('game.registration.sms_code_verification.email_sender_address'));
        $mail->setBody(sprintf($body, $code));

        $token = $this->config->get('game.registration.sms_code_verification.email_to_sms_token');
        if ($token) {
            $mail->setSubject($token);
        }

        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (RuntimeException) {
            //nothing to do here
        }
    }
}
