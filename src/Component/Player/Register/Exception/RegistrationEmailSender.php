<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Orm\Entity\UserInterface;

final class RegistrationEmailSender implements RegistrationEmailSenderInterface
{

    public function send(UserInterface $player, string $password): void
    {
        $text = "Hallo " . $player->getLogin() . "!\n\r\n\r";
        $text .= "Vielen Dank für Deine Anmeldung bei Star Trek Universe. Du kannst Dich nun mit folgendem Passwort und Deinem gewählten Loginnamen einloggen.\n\r\n\r";
        $text .= "Login: " . $player->getLogin() . "\n\r";
        $text .= "Passwort: " . $password . "\n\r\n\r";
        $text .= "Bitte ändere das Passwort und auch Deinen Siedlernamen gleich nach Deinem Login.\n\r";
        $text .= "Und nun wünschen wir Dir viel Spaß!\n\r\n\r";
        $text .= "Das STU-Team\r\n\r\n";
        $text .= "https://stu.wolvnet.de";

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/plain; charset=utf-8\r\n";
        $header .= "To: " . $player->getEmail() . " <" . $player->getEmail() . ">\r\n";
        $header .= "From: Star Trek Universe <automailer@stuniverse.de>\r\n";

        mail($player->getEmail(), "Star Trek Universe Anmeldung", $text, $header);
    }
}