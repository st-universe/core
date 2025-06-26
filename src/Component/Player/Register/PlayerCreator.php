<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Player\Register\Exception\EmailAddressInvalidException;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\Exception\MobileNumberInvalidException;
use Stu\Component\Player\Register\Exception\PlayerDuplicateException;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserRefererRepositoryInterface;

/**
 * Creates players with registration and optional sms validation
 */
class PlayerCreator implements PlayerCreatorInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected PlayerDefaultsCreatorInterface $playerDefaultsCreator,
        private RegistrationEmailSenderInterface $registrationEmailSender,
        private SmsVerificationCodeSenderInterface $smsVerificationCodeSender,
        private StuHashInterface $stuHash,
        private EntityManagerInterface $entityManager,
        private UserRefererRepositoryInterface $userRefererRepository
    ) {}

    #[Override]
    public function createWithMobileNumber(
        string $loginName,
        string $emailAddress,
        Faction $faction,
        string $mobile,
        string $password,
        ?string $referer = null
    ): void {
        $mobileWithDoubleZero = str_replace('+', '00', $mobile);
        $this->checkForException($loginName, $emailAddress, $mobileWithDoubleZero);

        $randomSmsHash = substr(md5(uniqid((string) random_int(0, mt_getrandmax()), true)), 16, 6);
        $randomEmailHash = substr(md5(uniqid((string) random_int(0, mt_getrandmax()), true)), 16, 6);

        $player = $this->createPlayer(
            $loginName,
            $emailAddress,
            $faction,
            $password,
            $mobileWithDoubleZero,
            $randomSmsHash,
            $randomEmailHash,
            $referer
        );

        $this->smsVerificationCodeSender->send($player, $randomSmsHash);
    }

    private function checkForException(string $loginName, string $emailAddress, ?string $mobile = null): void
    {
        if (
            !preg_match('/^[a-zA-Z0-9]+$/i', $loginName) ||
            mb_strlen($loginName) < 6
        ) {
            throw new LoginNameInvalidException();
        }
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new EmailAddressInvalidException();
        }
        if ($this->userRepository->getByLogin($loginName) || $this->userRepository->getByEmail($emailAddress)) {
            throw new PlayerDuplicateException();
        }
        if ($mobile !== null && $this->userRepository->getByMobile($mobile, $this->stuHash->hash($mobile))) {
            throw new PlayerDuplicateException();
        }
        if ($mobile !== null && (!$this->isMobileNumberCountryAllowed($mobile) || !$this->isMobileFormatCorrect($mobile))) {
            throw new MobileNumberInvalidException();
        }
    }

    private function isMobileNumberCountryAllowed(string $mobile): bool
    {
        return strpos($mobile, '0049') === 0 || strpos($mobile, '0041') === 0 || strpos($mobile, '0043') === 0;
    }

    private function isMobileFormatCorrect(string $mobile): bool
    {
        return (bool) preg_match('/00..[1-9]\d/', $mobile);
    }

    #[Override]
    public function createPlayer(
        string $loginName,
        string $emailAddress,
        Faction $faction,
        string $password,
        ?string $mobile = null,
        ?string $smsCode = null,
        ?string $emailCode = null,
        ?string $referer = null
    ): User {

        $player = $this->userRepository->prototype();
        $player->setFaction($faction);

        $this->userRepository->save($player);
        $this->entityManager->flush();

        $player->setUsername('Siedler ' . $player->getId());

        $registration = $player->getRegistration();
        $registration->setLogin($loginName);
        $registration->setEmail($emailAddress);
        $registration->setCreationDate(time());
        $registration->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $registration->setEmailCode($emailCode);

        $player->setState(UserEnum::USER_STATE_ACCOUNT_VERIFICATION);

        // set player state to awaiting sms code if mobile provided
        if ($mobile !== null) {
            $registration->setMobile($mobile);
            $registration->setSmsCode($smsCode);
            $player->setState(UserEnum::USER_STATE_ACCOUNT_VERIFICATION);
        }

        if ($referer !== null) {
            $this->saveReferer($registration, $referer);
        }

        $this->userRepository->save($player);

        $this->playerDefaultsCreator->createDefault($player);
        if ($emailCode) {
            $this->registrationEmailSender->send($player, $emailCode);
        }

        return $player;
    }

    private function saveReferer(UserRegistration $registration, ?string $referer): void
    {
        if ($referer !== null) {

            $sanitizedReferer = preg_replace('/[^\p{L}\p{N}\s]/u', '', $referer);
            $sanitizedReferer = $sanitizedReferer !== null ? substr($sanitizedReferer, 0, 2000) : '';

            $userReferer = $this->userRefererRepository->prototype();
            $userReferer->setUserRegistration($registration);
            $userReferer->setReferer($sanitizedReferer);

            $this->userRefererRepository->save($userReferer);
        }
    }
}
