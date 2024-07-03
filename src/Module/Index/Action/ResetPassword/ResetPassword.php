<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\ResetPassword;

use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Exception\InvalidParamException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Index\View\ShowLostPassword\ShowLostPassword;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ResetPassword implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RESET_PASSWORD';

    public function __construct(private ResetPasswordRequestInterface $resetPasswordRequest, private ConfigInterface $config, private UserRepositoryInterface $userRepository, private PasswordGeneratorInterface $passwordGenerator)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $token = $this->resetPasswordRequest->getToken();

        $user = $this->userRepository->getByResetToken($token);

        if ($user === null) {
            throw new InvalidParamException();
        }
        $password = $this->passwordGenerator->generatePassword();

        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $user->setPasswordToken('');

        $this->userRepository->save($user);

        $game->setView(ShowLostPassword::VIEW_IDENTIFIER);
        $game->addInformation(_('Es wurde ein neues Passwort generiert und an die eMail-Adresse geschickt'));

        $body = <<<EOT
            Hallo.\n\n
            Du kannst Dich ab sofort mit folgendem Passwort in Star Trek Universe einloggen: %s\n\n
            Das Star Trek Universe Team\n
            %s,
            EOT;

        $mail = new Message();
        $mail->addTo($user->getEmail());
        $mail->setSubject(_('Star Trek Universe - Neues Passwort'));
        $mail->setFrom($this->config->get('game.email_sender_address'));
        $mail->setBody(
            sprintf(
                $body,
                $password,
                $this->config->get('game.base_url'),
            )
        );
        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (RuntimeException) {
            $game->addInformation(_('Die eMail konnte nicht verschickt werden'));
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
