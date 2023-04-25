<?php

namespace Stu\Orm\Repository;

use DateTimeInterface;
use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitation;
use Stu\Orm\Entity\UserInvitationInterface;

/**
 * @extends ObjectRepository<UserInvitation>
 *
 * @method null|UserInvitationInterface find(integer $id)
 * @method UserInvitationInterface[] findAll()
 */
interface UserInvitationRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserInvitationInterface;

    public function save(UserInvitationInterface $userInvitation): void;

    /**
     * @return list<UserInvitationInterface>
     */
    public function getInvitationsByUser(UserInterface $user): array;

    public function getByToken(string $token): ?UserInvitationInterface;

    public function truncateExpiredTokens(DateTimeInterface $ttl): void;

    public function truncateAllEntries(): void;
}
