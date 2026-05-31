<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\SpacecraftLogScan;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<SpacecraftLogScan>
 */
final class SpacecraftLogScanRepository extends EntityRepository implements SpacecraftLogScanRepositoryInterface
{
    #[\Override]
    public function prototype(): SpacecraftLogScan
    {
        return new SpacecraftLogScan();
    }

    #[\Override]
    public function save(SpacecraftLogScan $spacecraftLogScan): void
    {
        $this->getEntityManager()->persist($spacecraftLogScan);
    }

    #[\Override]
    public function saveScan(User $user, int $spacecraftId, int $date): void
    {
        $spacecraftLogScan = $this->findOneBy([
            'user' => $user,
            'spacecraft_id' => $spacecraftId
        ]);

        if ($spacecraftLogScan === null) {
            $spacecraftLogScan = $this->prototype()
                ->setUser($user)
                ->setSpacecraftId($spacecraftId);
        }

        $spacecraftLogScan->setDate($date);
        $this->save($spacecraftLogScan);
    }
}
