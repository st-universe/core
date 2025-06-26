<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TachyonScan;

/**
 * @extends ObjectRepository<TachyonScan>
 *
 * @method null|TachyonScan find(integer $id)
 */
interface TachyonScanRepositoryInterface extends ObjectRepository
{
    public function prototype(): TachyonScan;

    public function save(TachyonScan $obj): void;

    public function isTachyonScanActiveByShipLocationAndOwner(Spacecraft $spacecraft): bool;

    public function deleteOldScans(int $threshold): void;
}
