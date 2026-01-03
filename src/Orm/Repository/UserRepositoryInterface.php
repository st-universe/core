<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<User>
 *
 * @method null|User find(integer $id)
 * @method User[] findAll()
 */
interface UserRepositoryInterface extends ObjectRepository
{
    public function prototype(): User;

    public function save(User $post): void;

    public function delete(User $post): void;

    public function getByResetToken(string $resetToken): ?User;

    /**
     * @param array<int> $ignoreIds
     *
     * @return array<int, User>
     */
    public function getDeleteable(
        int $idleTimeThreshold,
        int $idleTimeVacationThreshold,
        array $ignoreIds
    ): array;

    /**
     * @return array<int, User>
     */
    public function getIdleRegistrations(
        int $idleTimeThreshold
    ): array;

    public function getByEmail(string $email): ?User;

    public function getByMobile(string $mobile, string $mobileHash): ?User;

    public function getByLogin(string $loginName): ?User;

    /**
     * Returns all members of the given alliance
     *
     * @return array<User>
     */
    public function getByAlliance(Alliance $alliance): array;

    /**
     * @return array<User>
     */
    public function getList(
        string $sortField,
        string $sortOrder,
        ?int $limit,
        int $offset
    ): array;

    /**
     * @return array<User>
     */
    public function getNPCAdminList(
        string $sortField,
        string $sortOrder,
        ?int $limit,
        int $offset
    ): array;

    /**
     * @return array<User>
     */
    public function getFriendsByUserAndAlliance(User $user, ?Alliance $alliance): array;

    /**
     * @return array<User>
     */
    public function getOrderedByLastaction(int $limit, int $ignoreUserId, int $lastActionThreshold): array;

    public function getActiveAmount(): int;

    public function getInactiveAmount(int $days): int;

    public function getVacationAmount(): int;

    public function getActiveAmountRecentlyOnline(int $threshold): int;

    /**
     * @return array<User>
     */
    public function getNpcList(): array;

    /**
     * @return array<User>
     */
    public function getNonNpcList(): array;

    /**
     * @return array<User>
     */
    public function getNonNpcListbyFaction(int $factionid): array;

    /**
     * Returns the game's default fallback user item
     */
    public function getFallbackUser(): User;

    /**
     * @return array<User>
     */
    public function getUsersWithActiveLicense(int $tradePostId, int $currentTime, ?int $factionId = null): array;
}
