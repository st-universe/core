<?php

namespace Stu\Component\Player\Register;

use Stu\Component\Player\Register\Exception\RegistrationException;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;

interface PlayerCreatorInterface
{
    /**
     * @throws RegistrationException
     */
    public function createWithMobileNumber(
        string $loginName,
        string $emailAddress,
        FactionInterface $faction,
        string $mobile,
        ?string $referer = null
    ): void;

    public function createPlayer(
        string $loginName,
        string $emailAddress,
        FactionInterface $faction,
        string $password,
        ?string $mobile = null,
        ?string $smsCode = null,
        ?string $referer = null
    ): UserInterface;
}
