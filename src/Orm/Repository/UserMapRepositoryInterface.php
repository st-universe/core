<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserMap;

/**
 * @extends ObjectRepository<UserMap>
 */
interface UserMapRepositoryInterface extends ObjectRepository
{
    public function insertMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void;

    public function deleteMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void;

    public function getAmountByUser(int $userId, int $layerId): int;

    public function truncateByUser(int $userId, int $layerId): void;
}
