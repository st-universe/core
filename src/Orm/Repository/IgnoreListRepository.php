<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\IgnoreList;
use Stu\Orm\Entity\IgnoreListInterface;

/**
 * @extends EntityRepository<IgnoreList>
 */
final class IgnoreListRepository extends EntityRepository implements IgnoreListRepositoryInterface
{
    #[Override]
    public function prototype(): IgnoreListInterface
    {
        return new IgnoreList();
    }

    #[Override]
    public function save(IgnoreListInterface $ignoreList): void
    {
        $em = $this->getEntityManager();

        $em->persist($ignoreList);
    }

    #[Override]
    public function delete(IgnoreListInterface $ignoreList): void
    {
        $em = $this->getEntityManager();

        $em->remove($ignoreList);
        $em->flush();
    }

    #[Override]
    public function getByRecipient(int $recipientId): array
    {
        return $this->findBy(
            ['recipient' => $recipientId],
            ['user_id' => 'asc']
        );
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['recipient' => 'asc']
        );
    }

    #[Override]
    public function exists(int $userId, int $recipientId): bool
    {
        return $this->count([
            'user_id' => $userId,
            'recipient' => $recipientId
        ]) > 0;
    }

    #[Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s i WHERE i.user_id = :userId',
                    IgnoreList::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }
}
