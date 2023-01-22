<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\KnPostInterface;

/**
 * @extends EntityRepository<KnComment>
 */
final class KnCommentRepository extends EntityRepository implements KnCommentRepositoryInterface
{
    public function getByPost(int $postId): array
    {
        return $this->findBy(
            ['post_id' => $postId],
            ['id' => 'desc'],
        );
    }

    public function getAmountByPost(KnPostInterface $post): int
    {
        return $this->count(['post_id' => $post, 'deleted' => NULL]);
    }

    public function prototype(): KnCommentInterface
    {
        return new KnComment();
    }

    public function save(KnCommentInterface $comment): void
    {
        $em = $this->getEntityManager();

        $em->persist($comment);
        $em->flush();
    }

    public function delete(KnCommentInterface $comment): void
    {
        $em = $this->getEntityManager();

        $em->remove($comment);
        $em->flush();
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s c WHERE c.user_id = :userId',
                    KnComment::class
                )
            )
            ->setParameters(['userId' => $userId])
            ->execute();
    }
}
