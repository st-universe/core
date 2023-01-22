<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\AllianceBoardInterface;

/**
 * @extends ObjectRepository<AllianceBoard>
 *
 * @method null|AllianceBoardInterface find(integer $id)
 */
interface AllianceBoardRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceBoardInterface;

    public function save(AllianceBoardInterface $post): void;

    public function delete(AllianceBoardInterface $post): void;

    /**
     * @return AllianceBoardInterface[]
     */
    public function getByAlliance(int $allianceId): array;
}
