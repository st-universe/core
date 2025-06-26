<?php

namespace Stu\Orm\Repository;

use DateTimeInterface;
use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInvitation;

/**
 * @extends ObjectRepository<UserInvitation>
 *
 * @method null|UserInvitation find(integer $id)
 * @method UserInvitation[] findAll()
 */
interface UserInvitationRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserInvitation;

    public function save(UserInvitation $userInvitation): void;

    /**
     * @return list<UserInvitation>
     */
    public function getInvitationsByUser(User $user): array;

    public function getByToken(string $token): ?UserInvitation;

    public function truncateExpiredTokens(DateTimeInterface $ttl): void;

    public function truncateAllEntries(): void;
}
