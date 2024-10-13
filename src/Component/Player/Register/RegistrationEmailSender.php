<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Noodlehaus\ConfigInterface;
use Override;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Orm\Entity\UserInterface;

final class RegistrationEmailSender implements RegistrationEmailSenderInterface
{
    public function __construct(
        private MailFactoryInterface $mailFactory,
        private ConfigInterface $config
    ) {}

    #[Override]
    public function send(UserInterface $player, string $password): void
    {
        $body = <<<EOT
            Hallo %s\n\n
            Vielen Dank für Deine Anmeldung bei Star Trek Universe. Du kannst Dich nun mit folgendem Passwort und Deinem gewählten Loginnamen einloggen.\n\n
            Login:  %s\n
            Passwort:  %s\n\n
            Bitte ändere das Passwort und auch Deinen Spielernamen gleich nach Deinem ersten Login.\n
            Und nun wünschen wir Dir viel Spaß!\n\n
            Das STU-Team\n\n
            %s
            EOT;

        $mail = $this->mailFactory->createStuMail()
            ->withDefaultSender()
            ->addTo($player->getEmail())
            ->setSubject(_('Star Trek Universe - Anmeldung'))
            ->setBody(
                sprintf(
                    $body,
                    $player->getLogin(),
                    $player->getLogin(),
                    $password,
                    $this->config->get('game.base_url')
                )
            );
        try {
            $mail->send();
        } catch (RuntimeException) {
            //nothing to do here
        }
    }
}
