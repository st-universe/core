<?php

namespace Stu\Orm\Repository;

use DateTimeInterface;
use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;

/**
 * @method null|UserInvitationInterface find(integer $id)
 * @method UserInvitationInterface[] findAll()
 */
interface UserInvitationRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserInvitationInterface;

    public function save(UserInvitationInterface $userInvitation): void;

    public function getInvitationsByUser(UserInterface $user): array;

    public function getByToken(string $token): ?UserInvitationInterface;

    public function truncateExpiredTokens(DateTimeInterface $ttl): void;
}
