<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipInterface;
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

    public function isTachyonScanActiveByShipLocationAndOwner(ShipInterface $ship): bool;

    public function deleteOldScans(int $threshold): void;
}
