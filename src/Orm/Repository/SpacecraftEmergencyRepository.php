<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\SpacecraftEmergency;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;

/**
 * @extends EntityRepository<SpacecraftEmergency>
 */
final class SpacecraftEmergencyRepository extends EntityRepository implements SpacecraftEmergencyRepositoryInterface
{
    public function prototype(): SpacecraftEmergencyInterface
    {
        return new SpacecraftEmergency();
    }

    public function save(SpacecraftEmergencyInterface $spacecraftEmergency): void
    {
        $em = $this->getEntityManager();

        $em->persist($spacecraftEmergency);
    }

    public function getByShipId(int $shipId): ?SpacecraftEmergencyInterface
    {
        return $this->findOneBy([
            'ship_id' => $shipId, 'deleted' => NULL
        ]);
    }

    public function getActive(): array
    {
        return $this->findBy(
            ['deleted' => NULL],
            ['id' => 'desc']
        );
    }
}
