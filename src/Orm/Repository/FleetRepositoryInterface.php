<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Fleet>
 *
 * @method null|Fleet find(integer $id)
 * @method Fleet[] findAll()
 */
interface FleetRepositoryInterface extends ObjectRepository
{
    public function prototype(): Fleet;

    public function save(Fleet $fleet): void;

    public function delete(Fleet $fleet): void;

    public function truncateByUser(User $user): void;

    /**
     * @return array<Fleet>
     */
    public function getByUser(int $userId): array;

    public function getCountByUser(int $userId): int;

    public function getHighestSortByUser(int $userId): int;

    /**
     * @return array<Fleet>
     */
    public function getNonNpcFleetList(): array;

    public function truncateAllFleets(): void;
}
