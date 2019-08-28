<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\ResetPassword;

use InvalidParamException;
use Noodlehaus\ConfigInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Index\View\ShowLostPassword\ShowLostPassword;
use User;
use Zend\Mail\Exception\RuntimeException;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;

final class ResetPassword implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_RESET_PASSWORD';

    private $resetPasswordRequest;

    private $config;

    public function __construct(
        ResetPasswordRequestInterface $resetPasswordRequest,
        ConfigInterface $config
    ) {
        $this->resetPasswordRequest = $resetPasswordRequest;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $token = $this->resetPasswordRequest->getToken();
        $user = User::getByPasswordResetToken($token);
        if ($user === false) {
            throw new InvalidParamException;
        }
        $password = generatePassword();
        $user->setPassword(User::hashPassword($password));
        $user->setPasswordToken('');
        $user->save();
        $game->setView(ShowLostPassword::VIEW_IDENTIFIER);
        $game->addInformation(_('Es wurde ein neues Passwort generiert und an die eMail-Adresse geschickt'));

        $mail = new Message();
        $mail->addTo($user->getEmail());
        $mail->setSubject(_('Star Trek Universe - Neues Passwort'));
        $mail->setFrom('automailer@stuniverse.de');
        $mail->setBody(
            sprintf("Hallo.\n\n
Du kannst Dich ab sofort mit folgendem Passwort in Star Trek Universe einloggen: %s\n\n
Das Star Trek Universe Team\n
%s",
                $password,
                $this->config->get('game.base_path'),
            )
        );
        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (RuntimeException $e) {
            $game->addInformation(_('Die eMail konnte nicht verschickt werden'));
            return;
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
