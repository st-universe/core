<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\SpacecraftEmergency;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;

/**
 * @extends EntityRepository<SpacecraftEmergency>
 */
final class SpacecraftEmergencyRepository extends EntityRepository implements SpacecraftEmergencyRepositoryInterface
{
    #[Override]
    public function prototype(): SpacecraftEmergencyInterface
    {
        return new SpacecraftEmergency();
    }

    #[Override]
    public function save(SpacecraftEmergencyInterface $spacecraftEmergency): void
    {
        $em = $this->getEntityManager();

        $em->persist($spacecraftEmergency);
    }

    #[Override]
    public function getByShipId(int $shipId): ?SpacecraftEmergencyInterface
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
