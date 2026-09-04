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
    public function getByUserPair(User $firstUser, User $secondUser): array;

    /**
     * @return list<UserRelation>
     */
    public function getByAllianceAndUserPair(Alliance $alliance, User $user): array;

    /**
     * @param array<int> $typeIds
     */
    public function getActiveByUserPair(array $typeIds, User $firstUser, User $secondUser): ?UserRelation;

    /**
     * @param array<int> $typeIds
     */
    public function getActiveByAllianceAndUserPair(array $typeIds, Alliance $alliance, User $user): ?UserRelation;
}
