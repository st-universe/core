<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpUser;
use Stu\Orm\Entity\ShipRumpUserInterface;

/**
 * @extends EntityRepository<ShipRumpUser>
 */
final class ShipRumpUserRepository extends EntityRepository implements ShipRumpUserRepositoryInterface
{
    #[Override]
    public function isAvailableForUser(int $shipRumpId, int $userId): bool
    {
        return $this->count([
            'rump_id' => $shipRumpId,
            'user_id' => $userId,
        ]) > 0;
    }

    #[Override]
    public function prototype(): ShipRumpUserInterface
    {
        return new ShipRumpUser();
    }

    #[Override]
    public function save(ShipRumpUserInterface $shipRumpUser): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipRumpUser);
    }
}
