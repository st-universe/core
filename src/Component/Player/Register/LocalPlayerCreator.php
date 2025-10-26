<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;

/**
 * Creates players without any registration/validation
 */
class LocalPlayerCreator extends PlayerCreator
{
    #[\Override]
    public function createPlayer(
        string $loginName,
        string $emailAddress,
        Faction $faction,
        string $password,
        ?string $mobile = null,
        ?string $smsCode = null,
        ?string $emailCode = null,
        ?string $referrer = null
    ): User {

        $player = $this->userRepository->prototype();
        $player->setFaction($faction);

        $this->userRepository->save($player);
        $this->entityManager->flush();

        $player->setUsername(sprintf('Siedler %d', $player->getId()));

        $registration = $player->getRegistration();
        $registration->setLogin($loginName);
        $registration->setEmail($emailAddress);
        $registration->setCreationDate(time());
        $registration->setPassword(password_hash($password, PASSWORD_DEFAULT));

        $this->userRepository->save($player);

        $this->playerDefaultsCreator->createDefault($player);

        return $player;
    }
}
