<?php

namespace Stu\Component\Player\Register;

use Stu\Component\Player\Register\Exception\RegistrationException;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;

interface PlayerCreatorInterface
{
    /**
     * @throws RegistrationException
     */
    public function createWithMobileNumber(
        string $loginName,
        string $emailAddress,
        Faction $faction,
        string $mobile,
        string $password,
        ?string $referer = null
    ): void;

    public function createPlayer(
        string $loginName,
        string $emailAddress,
        Faction $faction,
        string $password,
        ?string $mobile = null,
        ?string $smsCode = null,
        ?string $referer = null
    ): User;
}
