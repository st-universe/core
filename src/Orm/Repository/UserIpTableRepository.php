<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserIpTableInterface;

/**
 * @extends EntityRepository<UserIpTable>
 */
final class UserIpTableRepository extends EntityRepository implements UserIpTableRepositoryInterface
{
    public function prototype(): UserIpTableInterface
    {
        return new UserIpTable();
    }

    public function save(UserIpTableInterface $userIpTable): void
    {
        $em = $this->getEntityManager();

        $em->persist($userIpTable);
        $em->flush();
    }

    public function findMostRecentByUser(UserInterface $user): ?UserIpTableInterface
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

    public function findBySessionId(string $sessionId): ?UserIpTableInterface
    {
        return $this->findOneBy([
            'session' => $sessionId
        ]);
    }

    public function truncateAllEntries(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s uit',
                UserIpTable::class
            )
        )->execute();
    }
}
