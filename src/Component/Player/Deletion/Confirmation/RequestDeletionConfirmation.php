<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Confirmation;

use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class RequestDeletionConfirmation implements RequestDeletionConfirmationInterface
{
    public function __construct(private UserRepositoryInterface $userRepository, private ConfigInterface $config, private StuHashInterface $stuHash)
    {
    }

    public function request(UserInterface $user): void
    {
        $token = $this->stuHash->hash(time() . $user->getCreationDate());

        $user->setDeletionMark(UserEnum::DELETION_REQUESTED);
        $user->setPasswordToken($token);

        $body = <<<EOT
            Hallo\n\n
            Du hast eine Accountlöschung in Star Trek Universe angefordert.\n\n
            Bitte bestätige die Löschung mittels Klick auf folgenden Link:\n
            %s/?CONFIRM_ACCOUNT_DELETION=1&token=%s\n
            Das STU-Team\n\n,
            EOT;

        $mail = new Message();
        $mail->addTo($user->getEmail());
        $mail->setSubject(_('Star Trek Universe - Accountlöschung'));
        $mail->setFrom($this->config->get('game.email_sender_address'));
        $mail->setBody(
            sprintf(
                $body,
                $this->config->get('game.base_url'),
                $token,
            )
        );

        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (RuntimeException) {
            return;
        }

        $this->userRepository->save($user);
    }
}
