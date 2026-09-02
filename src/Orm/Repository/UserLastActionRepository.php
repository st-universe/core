<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserLastAction;

/**
 * @extends EntityRepository<UserLastAction>
 */
final class UserLastActionRepository extends EntityRepository implements UserLastActionRepositoryInterface
{
    #[\Override]
    public function save(UserLastAction $lastAction): void
    {
        $em = $this->getEntityManager();
        $em->persist($lastAction);
    }
}
