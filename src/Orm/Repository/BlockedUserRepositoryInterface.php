<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BlockedUserInterface;

/**
 * @method null|BlockedUserInterface find(integer $id)
 */
interface BlockedUserRepositoryInterface extends ObjectRepository
{
    public function getByEmailHash(string $emailHash): ?BlockedUserInterface;

    public function getByMobileHash(string $mobileHash): ?BlockedUserInterface;

    public function save(BlockedUserInterface $blockedUser): void;

    public function prototype(): BlockedUserInterface;
}
