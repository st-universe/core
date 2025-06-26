<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserLayer;

/**
 * @extends ObjectRepository<UserLayer>
 *
 * @method null|UserLayer find(integer $id)
 */
interface UserLayerRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserLayer;

    public function save(UserLayer $userLayer): void;

    public function delete(UserLayer $userLayer): void;

    /**
     * @return list<UserLayer>
     */
    public function getByMappingType(int $mappingType): array;

    public function truncateAllUserLayer(): void;
}
