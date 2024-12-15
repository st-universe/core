<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftInterface;
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

    public function isTachyonScanActiveByShipLocationAndOwner(SpacecraftInterface $spacecraft): bool;

    public function deleteOldScans(int $threshold): void;
}
