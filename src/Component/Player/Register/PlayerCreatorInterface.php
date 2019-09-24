<?php

namespace Stu\Component\Player\Register;

use Stu\Component\Player\Register\Exception\RegistrationException;
use Stu\Orm\Entity\FactionInterface;

interface PlayerCreatorInterface
{
    /**
     * @throws RegistrationException
     */
    public function create(string $loginName, string $emailAddress, FactionInterface $faction): void;
}