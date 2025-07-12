<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\SmsManagement;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Component\Player\Register\SmsVerificationCodeSenderInterface;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\NoAccessCheckControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SmsManagement implements
    ActionControllerInterface,
    NoAccessCheckControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SMS_MANAGEMENT';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BlockedUserRepositoryInterface $blockedUserRepository,
        private SmsVerificationCodeSenderInterface $smsVerificationCodeSender,
        private StuHashInterface $stuHash,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $mobileInput = request::postString('mobile') ?: '';
        $countryCode = request::postString('countrycode') ?: '';

        $this->loggerUtil->log('SmsManagement wurde aufgerufen mit mobile: ' . ($mobileInput ?: 'LEER') . ' und countrycode: ' . ($countryCode ?: 'LEER'));

        $user = $game->getUser();

        if ($user->getState() !== UserStateEnum::USER_STATE_ACCOUNT_VERIFICATION) {
            $this->loggerUtil->log('User State ist nicht ACCOUNT_VERIFICATION: ' . $user->getState()->value);
            return;
        }

        $registration = $user->getRegistration();

        if ($registration->getMobile() === null) {
            throw new AccountNotVerifiedException('Keine Mobilnummer für SMS-Versand hinterlegt');
        }

        if ($registration->getSmsSended() >= 3) {
            throw new AccountNotVerifiedException('Alle SMS-Versuche sind aufgebraucht. Bitte kontaktiere den Support.');
        }

        $newMobile = $this->processMobileInput($mobileInput, $countryCode);
        $currentMobile = $registration->getMobile();

        if ($newMobile === '') {
            throw new AccountNotVerifiedException('Bitte gib eine Mobilnummer ein');
        }

        if ($newMobile === $currentMobile) {
            $this->resendSmsCode($user);
            return;
        }

        $this->updateMobileAndSend($user, $newMobile, $game);
    }

    private function processMobileInput(string $input, string $countryCode): string
    {
        if (empty($input)) {
            return '';
        }

        $cleanInput = str_replace(' ', '', trim($input, " \t\n\r\x0B"));

        $prefixesToRemove = ["+49", "+43", "+41"];
        foreach ($prefixesToRemove as $prefix) {
            if (strpos($cleanInput, $prefix) === 0) {
                $cleanInput = substr($cleanInput, strlen($prefix));
                break;
            }
        }

        $cleanInput = ltrim($cleanInput, '0');

        $processedMobile = str_replace('+', '00', $countryCode) . $cleanInput;

        return $processedMobile;
    }

    private function resendSmsCode(User $user): void
    {
        $registration = $user->getRegistration();
        $randomHash = substr(md5(uniqid((string) random_int(0, mt_getrandmax()), true)), 16, 6);

        $registration->setSmsCode($randomHash);
        $registration->setSmsSended($registration->getSmsSended() + 1);
        $this->userRepository->save($user);
        $this->entityManager->flush();

        $this->smsVerificationCodeSender->send($user, $randomHash);

        $mobile = $registration->getMobile();
        if ($mobile !== null) {
            throw new AccountNotVerifiedException('Der SMS-Verifikationscode wurde erneut an ' . $this->maskMobile($mobile) . ' versendet');
        }

        throw new AccountNotVerifiedException('Der SMS-Verifikationscode wurde erneut versendet');
    }

    private function updateMobileAndSend(User $user, string $newMobile, GameControllerInterface $game): void
    {
        if (!$this->isMobileNumberCountryAllowed($newMobile)) {
            throw new AccountNotVerifiedException('Nur deutsche (+49), österreichische (+43) und schweizer (+41) Nummern werden unterstützt');
        }

        if (!$this->isMobileFormatCorrect($newMobile)) {
            throw new AccountNotVerifiedException('Ungültiges Mobilnummer-Format');
        }

        $existingUser = $this->userRepository->getByMobile($newMobile, $this->stuHash->hash($newMobile));
        if ($existingUser !== null && $existingUser->getId() !== $user->getId()) {
            throw new AccountNotVerifiedException('Diese Mobilnummer ist bereits registriert');
        }

        if ($this->blockedUserRepository->getByMobileHash($this->stuHash->hash($newMobile)) !== null) {
            throw new AccountNotVerifiedException('Diese Mobilnummer ist blockiert');
        }

        $registration = $user->getRegistration();

        $registration->setMobile($newMobile);
        $registration->setSmsSended($registration->getSmsSended() + 1);
        $this->userRepository->save($user);
        $this->entityManager->flush();

        $this->loggerUtil->log('Mobilnummer wurde gespeichert: ' . $newMobile);

        $randomHash = substr(md5(uniqid((string) random_int(0, mt_getrandmax()), true)), 16, 6);
        $registration->setSmsCode($randomHash);
        $this->userRepository->save($user);
        $this->entityManager->flush();

        $this->smsVerificationCodeSender->send($user, $randomHash);

        throw new AccountNotVerifiedException('Mobilnummer wurde auf ' . $this->maskMobile($newMobile) . ' aktualisiert und eine neue SMS wurde versendet');
    }

    private function isMobileNumberCountryAllowed(string $mobile): bool
    {
        return strpos($mobile, '0049') === 0 || strpos($mobile, '0041') === 0 || strpos($mobile, '0043') === 0;
    }

    private function isMobileFormatCorrect(string $mobile): bool
    {
        return (bool) preg_match('/00..[1-9]\d/', $mobile);
    }

    private function maskMobile(string $mobile): string
    {
        if (strlen($mobile) < 8) {
            return $mobile;
        }

        $displayMobile = $mobile;
        if (strpos($mobile, '0049') === 0) {
            $displayMobile = '+49' . substr($mobile, 4);
        } elseif (strpos($mobile, '0043') === 0) {
            $displayMobile = '+43' . substr($mobile, 4);
        } elseif (strpos($mobile, '0041') === 0) {
            $displayMobile = '+41' . substr($mobile, 4);
        }

        if (strlen($displayMobile) > 8) {
            $start = substr($displayMobile, 0, 6);
            $end = substr($displayMobile, -2);
            $middle = str_repeat('*', strlen($displayMobile) - 8);
            return $start . $middle . $end;
        }

        return $displayMobile;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
