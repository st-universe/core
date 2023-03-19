<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Stu\Orm\Entity\UserInterface;

interface RegistrationEmailSenderInterface
{
    public function send(UserInterface $player, string $password): void;
}
