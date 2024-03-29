<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Buoy;
use Stu\Orm\Entity\BuoyInterface;

/**
 * @extends ObjectRepository<Buoy>
 */
interface BuoyRepositoryInterface extends ObjectRepository
{
    public function prototype(): BuoyInterface;

    public function save(BuoyInterface $buoy): void;

    public function delete(BuoyInterface $buoy): void;

    /**
     * @return list<BuoyInterface>
     */
    public function findByMapId(int $mapId): array;

    /**
     * @return list<BuoyInterface>
     */
    public function findBySysMapId(int $sysMapId): array;

    /**
     * @return list<BuoyInterface>
     */
    public function findByUserId(int $userId): array;
}