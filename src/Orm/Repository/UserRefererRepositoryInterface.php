<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserReferer;
use Stu\Orm\Entity\UserRefererInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<UserReferer>
 */
interface UserRefererRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserRefererInterface;

    public function save(UserRefererInterface $referer): void;

    public function delete(UserRefererInterface $referer): void;

    public function truncateAll(): void;

    /**
     * @return UserRefererInterface[]
     */
    public function getByUser(UserInterface $user): array;
}
