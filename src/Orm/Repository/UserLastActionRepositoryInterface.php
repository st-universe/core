<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserLastAction;

/**
 * @extends ObjectRepository<UserLastAction>
 *
 */
interface UserLastActionRepositoryInterface extends ObjectRepository
{
    public function save(UserLastAction $lastAction): void;
}
