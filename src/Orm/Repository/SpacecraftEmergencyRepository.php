<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\SpacecraftEmergency;

/**
 * @extends EntityRepository<SpacecraftEmergency>
 */
final class SpacecraftEmergencyRepository extends EntityRepository implements SpacecraftEmergencyRepositoryInterface
{
    #[Override]
    public function prototype(): SpacecraftEmergency
    {
        return new SpacecraftEmergency();
    }

    #[Override]
    public function save(SpacecraftEmergency $spacecraftEmergency): void
    {
        $em = $this->getEntityManager();

        $em->persist($spacecraftEmergency);
    }

    #[Override]
    public function getByShipId(int $shipId): ?SpacecraftEmergency
    {
        return $this->findOneBy([
            'spacecraft_id' => $shipId,
            'deleted' => null
        ]);
    }

    #[Override]
    public function getActive(): array
    {
        return $this->findBy(
            ['deleted' => null],
            ['id' => 'desc']
        );
    }
}
