<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Entity\UserMap;

/**
 * @extends ObjectRepository<UserMap>
 */
interface UserMapRepositoryInterface extends ObjectRepository
{
    public function insertMapFieldsForUser(int $userId, int $layerId, int $cx, int $cy, int $range): void;

    public function getAmountByUser(UserInterface $user, LayerInterface $layer): int;

    public function truncateByUser(UserInterface $user): void;

    public function truncateByUserAndLayer(UserLayerInterface $userLayer): void;

    public function truncateAllUserMaps(): void;
}
