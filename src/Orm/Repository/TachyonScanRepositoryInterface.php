<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TachyonScan;
use Stu\Orm\Entity\TachyonScanInterface;

/**
 * @extends ObjectRepository<TachyonScan>
 *
 * @method null|TachyonScanInterface find(integer $id)
 */
interface TachyonScanRepositoryInterface extends ObjectRepository
{
    public function prototype(): TachyonScanInterface;

    public function save(TachyonScanInterface $obj): void;

    /**
     * @return array<array{id: int, user_id: int, map_id: int, starsystem_map_id: int, scan_time: int}>
     */
    public function findActiveByShipLocationAndOwner(ShipInterface $ship): array;

    public function deleteOldScans(int $threshold): void;
}
