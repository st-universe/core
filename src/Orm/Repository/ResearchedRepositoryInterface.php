<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ResearchedInterface;

interface ResearchedRepositoryInterface extends ObjectRepository
{
    public function hasUserFinishedResearch(int $researchId, int $userId): bool;

    public function getListByUser(int $userId): array;

    public function getCurrentResearch(int $userId): ?ResearchedInterface;

    public function getFor(int $researchId, int $userId): ?ResearchedInterface;

    public function save(ResearchedInterface $researched): void;

    public function delete(ResearchedInterface $researched): void;

    public function prototype(): ResearchedInterface;

    public function truncateForUser(int $userId): void;
}