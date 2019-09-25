<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

interface UserMapRepositoryInterface extends ObjectRepository
{
    public function insertMapFieldsForUser(int $userId, int $cx, int $cy, int $range): void;

    public function deleteMapFieldsForUser(int $userId, int $cx, int $cy, int $range): void;

    public function getAmountByUser(int $userId): int;

    public function truncateByUser(int $userId): void;
}