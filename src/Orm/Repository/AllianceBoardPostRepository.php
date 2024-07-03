<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AllianceBoardPost;
use Stu\Orm\Entity\AllianceBoardPostInterface;

/**
 * @extends EntityRepository<AllianceBoardPost>
 */
final class AllianceBoardPostRepository extends EntityRepository implements AllianceBoardPostRepositoryInterface
{
    #[Override]
    public function getRecentByBoard(int $boardId): ?AllianceBoardPostInterface
    {
        return $this->findOneBy(
            ['board_id' => $boardId],
            ['date' => 'desc']
        );
    }

    #[Override]
    public function getRecentByTopic(int $topicId): ?AllianceBoardPostInterface
    {
        return $this->findOneBy(
            ['topic_id' => $topicId],
            ['date' => 'desc']
        );
    }

    #[Override]
    public function getAmountByBoard(int $boardId): int
    {
        return $this->count([
            'board_id' => $boardId
        ]);
    }

    #[Override]
    public function getAmountByTopic(int $topicId): int
    {
        return $this->count([
            'topic_id' => $topicId
        ]);
    }

    #[Override]
    public function getByTopic(int $topicId, int $limit, int $offset): array
    {
        return $this->findBy(
            ['topic_id' => $topicId],
            ['date' => 'asc'],
            $limit,
            $offset
        );
    }

    #[Override]
    public function prototype(): AllianceBoardPostInterface
    {
        return new AllianceBoardPost();
    }

    #[Override]
    public function save(AllianceBoardPostInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    #[Override]
    public function delete(AllianceBoardPostInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }
}
