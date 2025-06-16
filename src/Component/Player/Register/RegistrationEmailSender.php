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
    public function send(UserInterface $player, string $activationCode): void
    {
        $body = <<<EOT
            Hallo %s\n\n
            Vielen Dank für Deine Anmeldung bei Star Trek Universe. Um Deinen Account zu aktivieren, verwende bitte folgenden Aktivierungscode:\n\n
            Aktivierungscode: %s\n\n
            Gib diesen Code auf der Aktivierungsseite ein, um Deinen Account freizuschalten.\n
            Und nun wünschen wir Dir viel Spaß!\n\n
            Das STU-Team\n\n
            %s
            EOT;

        $mail = $this->mailFactory->createStuMail()
            ->withDefaultSender()
            ->addTo($player->getRegistration()->getEmail())
            ->setSubject(_('Star Trek Universe - Account aktivieren'))
            ->setBody(
                sprintf(
                    $body,
                    $player->getRegistration()->getLogin(),
                    $activationCode,
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
