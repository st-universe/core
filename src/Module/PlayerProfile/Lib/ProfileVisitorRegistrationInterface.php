<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Lib;


use Stu\Orm\Entity\UserInterface;

interface ProfileVisitorRegistrationInterface
{
    /**
     * Adds a profile visit entry if user and visitor are not the same
     */
    public function register(UserInterface $user, UserInterface $visitor): void;
}