<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Register\Exception\EmailAddressInvalidException;
use Stu\Component\Player\Register\Exception\InvitationTokenInvalidException;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\Exception\MobileNumberInvalidException;
use Stu\Component\Player\Register\Exception\PlayerDuplicateException;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlayerCreator implements PlayerCreatorInterface
{
    private UserRepositoryInterface $userRepository;

    private PlayerDefaultsCreatorInterface $playerDefaultsCreator;

    private RegistrationEmailSenderInterface $registrationEmailSender;

    private SmsVerificationCodeSenderInterface $smsVerificationCodeSender;

    private UserInvitationRepositoryInterface $userInvitationRepository;

    private ConfigInterface $config;

    private PasswordGeneratorInterface $passwordGenerator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PlayerDefaultsCreatorInterface $playerDefaultsCreator,
        RegistrationEmailSenderInterface $registrationEmailSender,
        SmsVerificationCodeSenderInterface $smsVerificationCodeSender,
        UserInvitationRepositoryInterface $userInvitationRepository,
        ConfigInterface $config,
        PasswordGeneratorInterface $passwordGenerator
    ) {
        $this->userRepository = $userRepository;
        $this->playerDefaultsCreator = $playerDefaultsCreator;
        $this->registrationEmailSender = $registrationEmailSender;
        $this->smsVerificationCodeSender = $smsVerificationCodeSender;
        $this->userInvitationRepository = $userInvitationRepository;
        $this->config = $config;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function createViaToken(
        string $loginName,
        string $emailAddress,
        FactionInterface $faction,
        string $token
    ): void {
        $this->checkForException($loginName, $emailAddress);

        $invitation = $this->userInvitationRepository->getByToken($token);

        if ($invitation === null || !$invitation->isValid($this->config->get('game.invitation.ttl'))) {
            throw new InvitationTokenInvalidException();
        }

        $player = $this->createPlayer(
            $loginName,
            $emailAddress,
            $faction
        );

        $invitation->setInvitedUserId($player->getId());

        $this->userInvitationRepository->save($invitation);
    }

    public function createWithMobileNumber(
        string $loginName,
        string $emailAddress,
        FactionInterface $faction,
        string $mobile
    ): void {
        $mobileWithDoubleZero = str_replace('+', '00', $mobile);
        $this->checkForException($loginName, $emailAddress, $mobileWithDoubleZero);

        $randomHash = substr(md5(uniqid(strval(rand()), true)), 16, 6);

        $player = $this->createPlayer(
            $loginName,
            $emailAddress,
            $faction,
            $mobileWithDoubleZero,
            $randomHash
        );

        $this->smsVerificationCodeSender->send($player, $randomHash);
    }

    private function checkForException($loginName, $emailAddress, $mobile = null): void
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
        if ($this->userRepository->getByLogin($loginName) || $this->userRepository->getByEmail($emailAddress) || $this->userRepository->getByMobile($mobile)) {
            throw new PlayerDuplicateException();
        }
        if ($mobile !== null) {
            if (!$this->isMobileNumberCountryAllowed($mobile) || !$this->isMobileFormatCorrect($mobile)) {
                throw new MobileNumberInvalidException();
            }
        }
    }

    private function isMobileNumberCountryAllowed(string $mobile): bool
    {
        return strpos($mobile, '0049') === 0 || strpos($mobile, '0041') === 0 || strpos($mobile, '0043') === 0;
    }

    private function isMobileFormatCorrect(string $mobile): bool
    {
        return !preg_match('/[^0-9]/', $mobile);
    }

    public function createPlayer(
        string $loginName,
        string $emailAddress,
        FactionInterface $faction,
        string $mobile = null,
        string $smsCode = null
    ): UserInterface {
        $player = $this->userRepository->prototype();
        $player->setLogin(mb_strtolower($loginName));
        $player->setEmail($emailAddress);
        $player->setFaction($faction);

        $this->userRepository->save($player);

        $password = $this->passwordGenerator->generatePassword();

        $player->setUsername('Siedler ' . $player->getId());
        $player->setTick(1);
        $player->setCreationDate(time());
        $player->setPassword(password_hash($password, PASSWORD_DEFAULT));

        // set player state to awaiting sms code
        if ($mobile !== null) {
            $player->setMobile($mobile);
            $player->setSmsCode($smsCode);
            $player->setActive(UserEnum::USER_STATE_SMS_VERIFICATION);
        }

        $this->userRepository->save($player);

        $this->playerDefaultsCreator->createDefault($player);
        $this->registrationEmailSender->send($player, $password);

        return $player;
    }
}
