<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TachyonScanInterface;
use Stu\Orm\Entity\TachyonScan;

final class TachyonScanRepository extends EntityRepository implements TachyonScanRepositoryInterface
{
    public function prototype(): TachyonScanInterface
    {
        return new TachyonScan();
    }
}
