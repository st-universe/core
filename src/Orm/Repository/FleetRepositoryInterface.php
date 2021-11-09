<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\UserInterface;

/**
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
     * @return FleetInterface[]
     */
    public function getByUser(int $userId): iterable;

    public function getHighestSortByUser(int $userId): int;

    public function getNonNpcFleetList(): iterable;
}
