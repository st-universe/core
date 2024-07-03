<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Entity\AllianceBoardTopicInterface;

/**
 * @extends EntityRepository<AllianceBoardTopic>
 */
final class AllianceBoardTopicRepository extends EntityRepository implements AllianceBoardTopicRepositoryInterface
{
    #[Override]
    public function prototype(): AllianceBoardTopicInterface
    {
        return new AllianceBoardTopic();
    }

    #[Override]
    public function save(AllianceBoardTopicInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    #[Override]
    public function delete(AllianceBoardTopicInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function getRecentByAlliance(int $allianceId, int $limit = 3): array
    {
        return $this->findBy(
            ['alliance_id' => $allianceId],
            ['last_post_date' => 'desc'],
            $limit
        );
    }

    #[Override]
    public function getAmountByBoardId(int $boardId): int
    {
        return $this->count(['board_id' => $boardId]);
    }

    #[Override]
    public function getByBoardIdOrdered(int $boardId): array
    {
        return $this->findBy(
            ['board_id' => $boardId],
            ['sticky' => 'desc', 'last_post_date' => 'desc']
        );
    }
}
