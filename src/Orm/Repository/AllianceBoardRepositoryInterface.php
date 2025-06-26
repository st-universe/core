<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceBoard;

/**
 * @extends ObjectRepository<AllianceBoard>
 *
 * @method null|AllianceBoard find(integer $id)
 * @method AllianceBoard[] findAll()
 */
interface AllianceBoardRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceBoard;

    public function save(AllianceBoard $post): void;

    public function delete(AllianceBoard $post): void;

    /**
     * @return array<AllianceBoard>
     */
    public function getByAlliance(int $allianceId): array;

    public function truncateAllAllianceBoards(): void;
}
