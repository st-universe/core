<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<PrivateMessageFolder>
 */
final class PrivateMessageFolderRepository extends EntityRepository implements PrivateMessageFolderRepositoryInterface
{
    #[Override]
    public function prototype(): PrivateMessageFolderInterface
    {
        return new PrivateMessageFolder();
    }

    #[Override]
    public function save(PrivateMessageFolderInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    #[Override]
    public function delete(PrivateMessageFolderInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function getOrderedByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId, 'deleted' => null],
            ['sort' => 'asc'],
        );
    }

    #[Override]
    public function getByUserAndSpecial(int $userId, PrivateMessageFolderTypeEnum $folderType): ?PrivateMessageFolderInterface
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'special' => $folderType->value
        ]);
    }

    #[Override]
    public function getMaxOrderIdByUser(UserInterface $user): int
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
        )->setParameter('firstUserId', UserEnum::USER_FIRST_ID)
            ->execute();
    }
}
