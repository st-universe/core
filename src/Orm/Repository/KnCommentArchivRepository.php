<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\KnCommentArchiv;
use Stu\Orm\Entity\KnCommentArchivInterface;
use Stu\Orm\Entity\KnPostArchivInterface;

/**
 * @extends EntityRepository<KnCommentArchiv>
 */
final class KnCommentArchivRepository extends EntityRepository implements KnCommentArchivRepositoryInterface
{
    #[Override]
    public function getByPost(int $postId): array
    {
        return $this->findBy(
            ['post_id' => $postId],
            ['id' => 'desc'],
        );
    }

    #[Override]
    public function getAmountByPost(KnPostArchivInterface $post): int
    {
        return $this->count(['post_id' => $post, 'deleted' => null]);
    }

    #[Override]
    public function prototype(): KnCommentArchivInterface
    {
        return new KnCommentArchiv();
    }

    #[Override]
    public function save(KnCommentArchivInterface $comment): void
    {
        $em = $this->getEntityManager();

        $em->persist($comment);
        $em->flush();
    }

    #[Override]
    public function delete(KnCommentArchivInterface $comment): void
    {
        $em = $this->getEntityManager();

        $em->remove($comment);
        $em->flush();
    }

    #[Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s c WHERE c.user_id = :userId',
                    KnCommentArchiv::class
                )
            )
            ->setParameters(['userId' => $userId])
            ->execute();
    }
}
