<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\SessionStringInterface;
use Stu\Orm\Entity\UserInterface;

interface SessionStringRepositoryInterface extends ObjectRepository
{

    public function isValid(string $sessionString, int $userId): bool;

    public function truncate(UserInterface $user): void;

    public function prototype(): SessionStringInterface;

    public function save(SessionStringInterface $sessionString): void;
}
