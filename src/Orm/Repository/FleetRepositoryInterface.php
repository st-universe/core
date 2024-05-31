<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Fleet>
 *
 * @method null|FleetInterface find(integer $id)
 * @method FleetInterface[] findAll()
 */
interface FleetRepositoryInterface extends ObjectRepository
{
    public function prototype(): FleetInterface;

    public function save(FleetInterface $fleet): void;

    public function delete(FleetInterface $fleet): void;

    public function truncateByUser(UserInterface $user): void;

    /**
     * @return array<FleetInterface>
     */
    public function getByUser(int $userId): array;

    public function getCountByUser(int $userId): int;

    public function getHighestSortByUser(int $userId): int;

    /**
     * @return iterable<FleetInterface>
     */
    public function getNonNpcFleetList(): iterable;

    public function truncateAllFleets(): void;
}
