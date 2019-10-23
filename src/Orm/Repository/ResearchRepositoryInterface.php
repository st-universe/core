<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use phpDocumentor\Reflection\Types\Integer;
use Stu\Orm\Entity\ResearchInterface;

/**
 * @method null|ResearchInterface find(Integer $id)
 */
interface ResearchRepositoryInterface extends ObjectRepository
{
    /**
     * @return ResearchInterface[]
     */
    public function getAvailableResearch(int $userId): array;

    /**
     * @return ResearchInterface[]
     */
    public function getForFaction(int $factionId): array;

    public function getPlanetColonyLimitByUser(int $userId): int;

    public function getMoonColonyLimitByUser(int $userId): int;

    public function save(ResearchInterface $research): void;
}
