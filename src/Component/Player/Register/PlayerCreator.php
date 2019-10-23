<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Register\Exception\EmailAddressInvalidException;
use Stu\Component\Player\Register\Exception\InvitationTokenInvalidException;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\Exception\PlayerDuplicateException;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlayerCreator implements PlayerCreatorInterface
{
    private $userRepository;

    private $playerDefaultsCreator;

    private $registrationEmailSender;

    private $userInvitationRepository;

    private $config;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PlayerDefaultsCreatorInterface $playerDefaultsCreator,
        RegistrationEmailSenderInterface $registrationEmailSender,
        UserInvitationRepositoryInterface $userInvitationRepository,
        ConfigInterface $config
    ) {
        $this->userRepository = $userRepository;
        $this->playerDefaultsCreator = $playerDefaultsCreator;
        $this->registrationEmailSender = $registrationEmailSender;
        $this->userInvitationRepository = $userInvitationRepository;
        $this->config = $config;
    }

    public function create(
        string $loginName,
        string $emailAddress,
        FactionInterface $faction,
        string $token
    ): void
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

        $invitation = $this->userInvitationRepository->getByToken($token);

        if ($invitation === null || !$invitation->isValid($this->config->get('game.invitation.ttl'))) {
            throw new InvitationTokenInvalidException();
        }

        $player = $this->userRepository->prototype();
        $player->setLogin($loginName);
        $player->setEmail($emailAddress);
        $player->setFaction($faction);

        $this->userRepository->save($player);

        $invitation->setInvitedUser($player);

        $this->userInvitationRepository->save($invitation);

        $player->setUser('Siedler ' . $player->getId());
        $player->setTick(1);
        // @todo
        // $player->setTick(rand(1,8));
        $player->setCreationDate(time());

        $password = generatePassword();
        $player->setPassword(sha1($password));

        $this->userRepository->save($player);

        $this->playerDefaultsCreator->createDefault($player);
        $this->registrationEmailSender->send($player, $password);
    }
}
