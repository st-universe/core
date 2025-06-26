<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BlockedUser;

/**
 * @extends ObjectRepository<BlockedUser>
 *
 * @method null|BlockedUser find(integer $id)
 * @method BlockedUser[] findAll()
 */
interface BlockedUserRepositoryInterface extends ObjectRepository
{
    public function getByEmailHash(string $emailHash): ?BlockedUser;

    public function getByMobileHash(string $mobileHash): ?BlockedUser;

    public function save(BlockedUser $blockedUser): void;

    public function prototype(): BlockedUser;
}
