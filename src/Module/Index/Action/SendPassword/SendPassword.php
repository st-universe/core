<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\SendPassword;

use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Index\View\ShowLostPassword\ShowLostPassword;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SendPassword implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SEND_PASSWORD';

    private SendPasswordRequestInterface $sendPasswordRequest;

    private UserRepositoryInterface $userRepository;

    private StuHashInterface $stuHash;

    private ConfigInterface $config;

    public function __construct(
        SendPasswordRequestInterface $sendPasswordRequest,
        UserRepositoryInterface $userRepository,
        StuHashInterface $stuHash,
        ConfigInterface $config
    ) {
        $this->sendPasswordRequest = $sendPasswordRequest;
        $this->userRepository = $userRepository;
        $this->stuHash = $stuHash;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowLostPassword::VIEW_IDENTIFIER);

        $emailAddress = $this->sendPasswordRequest->getEmailAddress();

        if (mb_strlen($emailAddress) == 0) {
            $game->addInformation(_('Die eMail-Adresse ist nicht gültig'));
            return;
        }
        $user = $this->userRepository->getByEmail($emailAddress);
        if ($user === null) {
            $game->addInformation(_('Die eMail-Adresse ist nicht gültig'));
            return;
        }

        $token = $this->stuHash->hash(time() . $user->getLogin());
        $user->setPasswordToken($token);

        $this->userRepository->save($user);

        $mail = new Message();
        $mail->addTo($user->getEmail());
        $mail->setSubject(_('Star Trek Universe - Password vergessen'));
        $mail->setFrom($this->config->get('game.email_sender_address'));
        $mail->setBody(
            sprintf(
                "Hallo.\n\n
Du bekommst diese eMail, da Du in Star Trek Universe ein neues Password angefordert hast. Solltest Du das nicht getan
haben, so ignoriere die eMail einfach.\n\n
Klicke auf folgenden Link um Dir ein neues Password zu setzen:\n
%s/?SHOW_RESET_PASSWORD=1&TOKEN=%s\n\n
Das Star Trek Universe Team\n
%s",
                $this->config->get('game.base_url'),
                $token,
                $this->config->get('game.base_url'),
            )
        );
        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (RuntimeException $e) {
            $game->addInformation(_('Die eMail konnte nicht verschickt werden'));
            return;
        }
        $game->addInformation(_('Die eMail wurde verschickt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
