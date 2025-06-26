<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceBoardTopic;

/**
 * @extends ObjectRepository<AllianceBoardTopic>
 */
interface AllianceBoardTopicRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceBoardTopic;

    public function save(AllianceBoardTopic $post): void;

    public function delete(AllianceBoardTopic $post): void;

    /**
     * @return list<AllianceBoardTopic>
     */
    public function getRecentByAlliance(int $allianceId, int $limit = 3): array;

    public function getAmountByBoardId(int $boardId): int;

    /**
     * @return list<AllianceBoardTopic>
     */
    public function getByBoardIdOrdered(int $boardId): array;
}
