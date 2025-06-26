<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserReferer;

/**
 * @extends ObjectRepository<UserReferer>
 */
interface UserRefererRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserReferer;

    public function save(UserReferer $referer): void;

    public function delete(UserReferer $referer): void;

    public function truncateAll(): void;
}
