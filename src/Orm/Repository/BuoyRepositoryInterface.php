<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Buoy;
use Stu\Orm\Entity\BuoyInterface;

/**
 * @extends ObjectRepository<Buoy>
 * 
 * @method null|BuoyInterface find(integer $id)
 */
interface BuoyRepositoryInterface extends ObjectRepository
{
    public function prototype(): BuoyInterface;

    public function save(BuoyInterface $buoy): void;

    public function delete(BuoyInterface $buoy): void;

    /**
     * @return array<BuoyInterface>
     */
    public function findByUserId(int $userId): array;
}
