<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\SendPassword;

use Noodlehaus\ConfigInterface;
use Override;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Index\View\ShowLostPassword\ShowLostPassword;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SendPassword implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SEND_PASSWORD';

    public function __construct(
        private SendPasswordRequestInterface $sendPasswordRequest,
        private UserRepositoryInterface $userRepository,
        private MailFactoryInterface $mailFactory,
        private StuHashInterface $stuHash,
        private ConfigInterface $config
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowLostPassword::VIEW_IDENTIFIER);

        $emailAddress = $this->sendPasswordRequest->getEmailAddress();

        if (mb_strlen($emailAddress) == 0) {
            $game->getInfo()->addInformation(_('Die eMail-Adresse ist nicht gültig'));
            return;
        }
        $user = $this->userRepository->getByEmail($emailAddress);
        if ($user === null) {
            $game->getInfo()->addInformation(_('Die eMail-Adresse ist nicht gültig'));
            return;
        }

        $registration = $user->getRegistration();

        $token = $this->stuHash->hash(time() . $registration->getLogin());
        $registration->setPasswordToken($token);

        $this->userRepository->save($user);

        $mail = $this->mailFactory->createStuMail()
            ->withDefaultSender()
            ->addTo($registration->getEmail())
            ->setSubject(_('Star Trek Universe - Password vergessen'))
            ->setBody(
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
            $mail->send();
        } catch (RuntimeException) {
            $game->getInfo()->addInformation(_('Die eMail konnte nicht verschickt werden'));
            return;
        }
        $game->getInfo()->addInformation(_('Die eMail wurde verschickt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
