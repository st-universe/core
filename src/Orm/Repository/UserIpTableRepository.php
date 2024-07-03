<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserIpTableInterface;

/**
 * @extends EntityRepository<UserIpTable>
 */
final class UserIpTableRepository extends EntityRepository implements UserIpTableRepositoryInterface
{
    #[Override]
    public function prototype(): UserIpTableInterface
    {
        return new UserIpTable();
    }

    #[Override]
    public function save(UserIpTableInterface $userIpTable): void
    {
        $em = $this->getEntityManager();

        $em->persist($userIpTable);
        $em->flush();
    }

    #[Override]
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

    #[Override]
    public function findBySessionId(string $sessionId): ?UserIpTableInterface
    {
        return $this->findOneBy([
            'session' => $sessionId
        ]);
    }

    #[Override]
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
