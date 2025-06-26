<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SessionString;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<SessionString>
 */
interface SessionStringRepositoryInterface extends ObjectRepository
{
    public function isValid(string $sessionString, int $userId): bool;

    public function truncate(User $user): void;

    public function prototype(): SessionString;

    public function save(SessionString $sessionString): void;
}
