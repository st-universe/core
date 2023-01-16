<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;

interface UserMapRepositoryInterface extends ObjectRepository
{
    public function insertMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void;

    public function deleteMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void;

    public function getAmountByUser(int $userId, int $layerId): int;

    public function truncateByUser(int $userId, int $layerId): void;
}
