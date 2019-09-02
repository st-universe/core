<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceBoardInterface;

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