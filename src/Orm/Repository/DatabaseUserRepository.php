<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DatabaseUser;
use Stu\Orm\Entity\DatabaseUserInterface;

final class DatabaseUserRepository extends EntityRepository implements DatabaseUserRepositoryInterface
{

    public function truncateByUserId(int $userId)
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s d where d.user_id = :userId',
                DatabaseUser::class
            )
        )
            ->setParameter('userId', $userId)
            ->execute();
    }

    public function findFor(int $databaseEntryId, int $userId): ?DatabaseUserInterface
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'database_id' => $databaseEntryId,
        ]);
    }

    public function exists(int $userId, int $databaseEntryId): bool
    {
        return $this->count([
                'user_id' => $userId,
                'database_id' => $databaseEntryId
            ]) > 0;
    }

    public function prototype(): DatabaseUserInterface
    {
        return new DatabaseUser();
    }

    public function save(DatabaseUserInterface $entry): void
    {
        $em = $this->getEntityManager();
        $em->persist($entry);
        $em->flush();
    }
}