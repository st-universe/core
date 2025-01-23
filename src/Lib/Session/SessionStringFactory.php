<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

class SessionStringFactory implements SessionStringFactoryInterface
{
    public function __construct(
        private SessionStringRepositoryInterface $sessionStringRepository,
        private StuTime $stuTime
    ) {}

    public function createSessionString(UserInterface $user): string
    {
        $string = bin2hex(random_bytes(15));

        $sessionString =
            $this->sessionStringRepository->prototype()
            ->setUser($user)
            ->setDate($this->stuTime->dateTime())
            ->setSessionString($string);

        $this->sessionStringRepository->save($sessionString);

        return $string;
    }
}
