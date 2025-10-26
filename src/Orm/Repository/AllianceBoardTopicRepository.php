<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceBoardTopic;

/**
 * @extends EntityRepository<AllianceBoardTopic>
 */
final class AllianceBoardTopicRepository extends EntityRepository implements AllianceBoardTopicRepositoryInterface
{
    #[\Override]
    public function prototype(): AllianceBoardTopic
    {
        return new AllianceBoardTopic();
    }

    #[\Override]
    public function save(AllianceBoardTopic $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    #[\Override]
    public function delete(AllianceBoardTopic $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[\Override]
    public function getRecentByAlliance(int $allianceId, int $limit = 3): array
    {
        return $this->findBy(
            ['alliance_id' => $allianceId],
            ['last_post_date' => 'desc'],
            $limit
        );
    }

    #[\Override]
    public function getAmountByBoardId(int $boardId): int
    {
        return $this->count(['board_id' => $boardId]);
    }

    #[\Override]
    public function getByBoardIdOrdered(int $boardId): array
    {
        return $this->findBy(
            ['board_id' => $boardId],
            ['sticky' => 'desc', 'last_post_date' => 'desc']
        );
    }
}
