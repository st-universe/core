<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRelation;

/**
 * @extends ObjectRepository<UserRelation>
 *
 * @method null|UserRelation find(integer $id)
 */
interface UserRelationRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserRelation;

    public function save(UserRelation $relation): void;

    public function delete(UserRelation $relation): void;

    public function truncateByUser(User $user): void;

    public function truncateByAlliance(Alliance $alliance): void;

    /**
     * @return list<UserRelation>
     */
    public function getByUserAndAlliance(User $user, ?Alliance $alliance): array;

    /**
     * @return list<UserRelation>
     */
    public function getByUserPair(int $firstUserId, int $secondUserId): array;

    /**
     * @return list<UserRelation>
     */
    public function getByAllianceAndUserPair(int $allianceId, int $userId): array;

    /**
     * @param array<int> $typeIds
     */
    public function getActiveByUserPair(array $typeIds, int $firstUserId, int $secondUserId): ?UserRelation;

    /**
     * @param array<int> $typeIds
     */
    public function getActiveByAllianceAndUserPair(array $typeIds, int $allianceId, int $userId): ?UserRelation;
}
