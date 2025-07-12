<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<PrivateMessageFolder>
 */
final class PrivateMessageFolderRepository extends EntityRepository implements PrivateMessageFolderRepositoryInterface
{
    #[Override]
    public function prototype(): PrivateMessageFolder
    {
        return new PrivateMessageFolder();
    }

    #[Override]
    public function save(PrivateMessageFolder $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    #[Override]
    public function delete(PrivateMessageFolder $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function getOrderedByUser(User $user): array
    {
        return $this->findBy(
            [
                'user_id' => $user->getId(),
                'deleted' => null
            ],
            ['sort' => 'asc'],
        );
    }

    #[Override]
    public function getByUserAndSpecial(int $userId, PrivateMessageFolderTypeEnum $folderType): ?PrivateMessageFolder
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'special' => $folderType->value
        ]);
    }

    #[Override]
    public function getMaxOrderIdByUser(User $user): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT MAX(pmf.sort) FROM %s pmf WHERE pmf.user = :user',
                PrivateMessageFolder::class
            )
        )->setParameters([
            'user' => $user
        ])->getSingleScalarResult();
    }

    #[Override]
    public function truncateAllNonNpcFolders(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s pmf
                WHERE pmf.user_id >= :firstUserId',
                PrivateMessageFolder::class
            )
        )->setParameter('firstUserId', UserConstants::USER_FIRST_ID)
            ->execute();
    }
}
