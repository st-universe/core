<?php

namespace Stu\Component\Player\Register;

use Stu\Orm\Entity\User;

interface RegistrationEmailSenderInterface
{
    public function send(User $player, string $activationCode): void;
}
