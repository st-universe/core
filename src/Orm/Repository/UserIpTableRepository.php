<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserIpTable;

/**
 * @extends EntityRepository<UserIpTable>
 */
final class UserIpTableRepository extends EntityRepository implements UserIpTableRepositoryInterface
{
    #[\Override]
    public function prototype(): UserIpTable
    {
        return new UserIpTable();
    }

    #[\Override]
    public function save(UserIpTable $userIpTable): void
    {
        $em = $this->getEntityManager();

        $em->persist($userIpTable);
        $em->flush();
    }

    #[\Override]
    public function findMostRecentByUser(User $user): ?UserIpTable
    {
        return $this->findOneBy(
            [
                'user' => $user
            ],
            [
                'id' => 'desc'
            ]
        );
    }

    #[\Override]
    public function findBySessionId(string $sessionId): ?UserIpTable
    {
        return $this->findOneBy([
            'session' => $sessionId
        ]);
    }
}
