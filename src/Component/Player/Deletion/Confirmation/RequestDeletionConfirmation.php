<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Confirmation;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

final class RequestDeletionConfirmation implements RequestDeletionConfirmationInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MailFactoryInterface $mailFactory,
        private ConfigInterface $config,
        private StuHashInterface $stuHash
    ) {}

    public function request(User $user): void
    {
        $registration = $user->getRegistration();
        $token = $this->stuHash->hash(time() . $registration->getCreationDate());

        $registration->setDeletionMark(UserConstants::DELETION_REQUESTED);
        $registration->setPasswordToken($token);

        $body = <<<EOT
            Hallo\n\n
            Du hast eine Accountlöschung in Star Trek Universe angefordert.\n\n
            Bitte bestätige die Löschung mittels Klick auf folgenden Link:\n
            %s/?CONFIRM_ACCOUNT_DELETION=1&token=%s\n
            Das STU-Team\n\n,
            EOT;

        $mail = $this->mailFactory->createStuMail()
            ->withDefaultSender()
            ->addTo($registration->getEmail())
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
