<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Entity\AllianceBoardTopicInterface;

/**
 * @extends ObjectRepository<AllianceBoardTopic>
 */
interface AllianceBoardTopicRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceBoardTopicInterface;

    public function save(AllianceBoardTopicInterface $post): void;

    public function delete(AllianceBoardTopicInterface $post): void;

    /**
     * @return AllianceBoardTopicInterface[]
     */
    public function getRecentByAlliance(int $allianceId, int $limit = 3): array;

    public function getAmountByBoardId(int $boardId): int;

    /**
     * @return AllianceBoardTopicInterface[]
     */
    public function getByBoardIdOrdered(int $boardId): array;
}
