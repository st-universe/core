<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BlockedUserInterface;

/**
 * @method null|BlockedUserInterface find(integer $id)
 */
interface BlockedUserRepositoryInterface extends ObjectRepository
{
    public function getByEmail(string $email): ?BlockedUserInterface;

    public function getByMobile(string $mobile): ?BlockedUserInterface;

    public function save(BlockedUserInterface $blockedUser): void;

    public function prototype(): BlockedUserInterface;
}
