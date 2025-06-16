<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\EmailManagement;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Component\Player\Register\RegistrationEmailSenderInterface;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\NoAccessCheckControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class EmailManagement implements
    ActionControllerInterface,
    NoAccessCheckControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EMAIL_MANAGEMENT';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RegistrationEmailSenderInterface $registrationEmailSender,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $emailInput = request::postString('email');
        $this->loggerUtil->log('EmailManagement wurde aufgerufen mit email: ' . ($emailInput ?: 'LEER'));

        $user = $game->getUser();

        if ($user->getState() !== UserEnum::USER_STATE_ACCOUNT_VERIFICATION) {
            $this->loggerUtil->log('User State ist nicht ACCOUNT_VERIFICATION: ' . $user->getState());
            return;
        }

        $registration = $user->getRegistration();
        $newEmail = trim(mb_strtolower($emailInput ?: ''));
        $currentEmail = $registration->getEmail();

        if ($newEmail === '') {
            throw new AccountNotVerifiedException('Bitte gib eine E-Mail-Adresse ein');
        }

        if ($newEmail === $currentEmail) {
            $this->resendActivationEmail($user);
            return;
        }

        $this->updateEmailAndSend($user, $newEmail);
    }

    private function resendActivationEmail(UserInterface $user): void
    {
        $registration = $user->getRegistration();
        $randomEmailHash = substr(md5(uniqid((string) random_int(0, mt_getrandmax()), true)), 16, 6);
        $registration->setEmailCode($randomEmailHash);

        $this->registrationEmailSender->send($user, $randomEmailHash);

        throw new AccountNotVerifiedException('Die Aktivierungs-E-Mail wurde erneut an ' . $registration->getEmail() . ' versendet');
    }

    private function updateEmailAndSend(UserInterface $user, string $newEmail): void
    {
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new AccountNotVerifiedException('Ungültige E-Mail-Adresse');
        }

        if (strlen($newEmail) < 8) {
            throw new AccountNotVerifiedException('E-Mail-Adresse ist zu kurz (mindestens 8 Zeichen)');
        }

        $existingUser = $this->userRepository->getByEmail($newEmail);
        if ($existingUser !== null && $existingUser->getId() !== $user->getId()) {
            throw new AccountNotVerifiedException('Diese E-Mail-Adresse ist bereits registriert');
        }

        $randomEmailHash = substr(md5(uniqid((string) random_int(0, mt_getrandmax()), true)), 16, 6);

        $registration = $user->getRegistration();
        $registration->setEmail($newEmail);
        $registration->setEmailCode($randomEmailHash);
        $this->userRepository->save($user);
        $this->entityManager->flush();

        $this->loggerUtil->log('E-Mail wurde gespeichert: ' . $newEmail);


        $this->registrationEmailSender->send($user, $randomEmailHash);

        throw new AccountNotVerifiedException('E-Mail-Adresse wurde auf ' . $newEmail . ' aktualisiert und eine neue Aktivierungs-E-Mail wurde versendet');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
