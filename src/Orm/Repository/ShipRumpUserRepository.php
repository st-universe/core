<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpUser;

/**
 * @extends EntityRepository<ShipRumpUser>
 */
final class ShipRumpUserRepository extends EntityRepository implements ShipRumpUserRepositoryInterface
{
    #[\Override]
    public function isAvailableForUser(int $rumpId, int $userId): bool
    {
        return $this->count([
            'rump_id' => $rumpId,
            'user_id' => $userId,
        ]) > 0;
    }

    #[\Override]
    public function prototype(): ShipRumpUser
    {
        return new ShipRumpUser();
    }

    #[\Override]
    public function save(ShipRumpUser $shipRumpUser): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipRumpUser);
    }
}
