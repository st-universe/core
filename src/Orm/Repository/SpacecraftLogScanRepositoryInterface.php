<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftLogScan;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<SpacecraftLogScan>
 */
interface SpacecraftLogScanRepositoryInterface extends ObjectRepository
{
    public function prototype(): SpacecraftLogScan;

    public function save(SpacecraftLogScan $spacecraftLogScan): void;

    public function saveScan(User $user, int $spacecraftId, int $date): void;
}
