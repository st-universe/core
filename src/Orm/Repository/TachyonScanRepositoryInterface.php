<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TachyonScanInterface;

/**
 * @method null|TachyonScanInterface find(integer $id)
 */
interface TachyonScanRepositoryInterface extends ObjectRepository
{
    public function prototype(): TachyonScanInterface;

    public function save(TachyonScanInterface $obj): void;

    public function findActiveByShipLocationAndOwner(ShipInterface $ship): array;
}
