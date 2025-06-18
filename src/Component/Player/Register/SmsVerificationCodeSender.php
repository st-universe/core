<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Noodlehaus\ConfigInterface;
use Override;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Orm\Entity\UserInterface;

final class SmsVerificationCodeSender implements SmsVerificationCodeSenderInterface
{
    public function __construct(
        private MailFactoryInterface $mailFactory,
        private ConfigInterface $config
    ) {}

    #[Override]
    public function send(UserInterface $player, string $code): void
    {
        $body = <<<EOT
            Dein Aktivierungscode für Star Trek Universe lautet:\n\n
            %s\n\n
            Viel Spaß!
            EOT;

        $mail = $this->mailFactory->createStuMail()
            ->setFrom($this->config->get('game.registration.sms_code_verification.email_sender_address'))
            ->addTo(sprintf('%s@%s', $player->getMobile(), $this->config->get('game.registration.sms_code_verification.email_to_sms_mail_domain')))
            ->setBody(sprintf($body, $code));

        $token = $this->config->get('game.registration.sms_code_verification.email_to_sms_token');
        if ($token) {
            $mail->setSubject($token);
        }

        try {
            $mail->send();
        } catch (RuntimeException) {
            //nothing to do here
        }
    }
}
