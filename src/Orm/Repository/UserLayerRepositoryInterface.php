<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserLayerInterface;

/**
 * @extends ObjectRepository<UserLayer>
 *
 * @method null|UserLayerInterface find(integer $id)
 */
interface UserLayerRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserLayerInterface;

    public function save(UserLayerInterface $userLayer): void;

    public function delete(UserLayerInterface $userLayer): void;

    /**
     * @return list<UserLayerInterface>
     */
    public function getByMappingType(int $mappingType): array;

    public function truncateByUser(int $userId): void;

    public function truncateAllUserLayer(): void;
}
