<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Buoy;

/**
 * @extends ObjectRepository<Buoy>
 *
 * @method null|Buoy find(integer $id)
 */
interface BuoyRepositoryInterface extends ObjectRepository
{
    public function prototype(): Buoy;

    public function save(Buoy $buoy): void;

    public function delete(Buoy $buoy): void;

    /**
     * @return array<Buoy>
     */
    public function findByUserId(int $userId): array;
}
