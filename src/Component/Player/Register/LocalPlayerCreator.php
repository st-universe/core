<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Override;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Creates players without any registration/validation
 */
class LocalPlayerCreator extends PlayerCreator
{
    #[Override]
    public function createPlayer(
        string $loginName,
        string $emailAddress,
        FactionInterface $faction,
        string $password,
        ?string $mobile = null,
        ?string $smsCode = null
    ): UserInterface {
        $player = $this->userRepository->prototype();
        $player->setLogin($loginName);
        $player->setEmail($emailAddress);
        $player->setFaction($faction);

        $this->userRepository->save($player);

        $player->setUsername(sprintf('Siedler %d', $player->getId()));
        $player->setTick(1);
        $player->setCreationDate(time());
        $player->setPassword(password_hash($password, PASSWORD_DEFAULT));

        $this->userRepository->save($player);

        $this->playerDefaultsCreator->createDefault($player);

        return $player;
    }
}
