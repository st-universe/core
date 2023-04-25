<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BlockedUser;
use Stu\Orm\Entity\BlockedUserInterface;

/**
 * @extends ObjectRepository<BlockedUser>
 *
 * @method null|BlockedUserInterface find(integer $id)
 * @method BlockedUserInterface[] findAll()
 */
interface BlockedUserRepositoryInterface extends ObjectRepository
{
    public function getByEmailHash(string $emailHash): ?BlockedUserInterface;

    public function getByMobileHash(string $mobileHash): ?BlockedUserInterface;

    public function save(BlockedUserInterface $blockedUser): void;

    public function prototype(): BlockedUserInterface;
}
