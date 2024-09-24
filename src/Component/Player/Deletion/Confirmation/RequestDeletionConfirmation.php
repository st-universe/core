<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Confirmation;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class RequestDeletionConfirmation implements RequestDeletionConfirmationInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MailFactoryInterface $mailFactory,
        private ConfigInterface $config,
        private StuHashInterface $stuHash
    ) {}

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

        $mail = $this->mailFactory->createStuMail()
            ->setFrom($this->config->get('game.email_sender_address'))
            ->addTo($user->getEmail())
            ->setSubject('Star Trek Universe - Accountlöschung')
            ->setBody(
                sprintf(
                    $body,
                    $this->config->get('game.base_url'),
                    $token,
                )
            );

        try {
            $mail->send();
        } catch (RuntimeException) {
            return;
        }

        $this->userRepository->save($user);
    }
}
