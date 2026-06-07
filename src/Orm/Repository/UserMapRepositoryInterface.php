<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserMap;

/**
 * @extends ObjectRepository<UserMap>
 */
interface UserMapRepositoryInterface extends ObjectRepository
{
    public function insertMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void;

    public function getAmountByUser(User $user, Layer $layer): int;

    /**
     * @return array<int, array{y: int, startX: int, endX: int}>
     */
    public function getVisibleMapFieldRuns(int $userId, int $layerId): array;

    public function truncateByUser(User $user): void;

    public function truncateByUserAndLayer(UserLayer $userLayer): void;
}
