<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserInterface;

/**
 * @method null|UserInterface find(integer $id)
 */
interface UserRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserInterface;

    public function save(UserInterface $post): void;

    public function delete(UserInterface $post): void;

    public function getAmountByFaction(int $factionId): int;

    public function getByResetToken(string $resetToken): ?UserInterface;

    /**
     * @return UserInterface[]
     */
    public function getActualPlayer(): iterable;

    /**
     * @return UserInterface[]
     */
    public function getIdlePlayer(
        int $idleTimeThreshold,
        array $ignoreIds
    ): iterable;

    public function getByEmail(string $email): ?UserInterface;

    public function getByLogin(string $loginName): ?UserInterface;

    /**
     * @return UserInterface[]
     */
    public function getByAlliance(int $allianceId): iterable;

    /**
     * @return UserInterface[]
     */
    public function getByMappingType(int $mappingType): iterable;

    /**
     * @return UserInterface[]
     */
    public function getList(
        string $sortField,
        string $sortOrder,
        int $limit,
        int $offset
    ): iterable;

    /**
     * @return UserInterface[]
     */
    public function getFriendsByUserAndAlliance(int $userId, int $allianceId): iterable;

    /**
     * @return UserInterface[]
     */
    public function getOrderedByLastaction(int $limit, int $ignoreUserId, int $lastActionThreshold): iterable;

    public function getActiveAmount(): int;

    public function getActiveAmountRecentlyOnline(int $threshold): int;
}