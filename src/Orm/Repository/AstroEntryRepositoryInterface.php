<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AstronomicalEntryInterface;

/**
 * @extends ObjectRepository<AstronomicalEntry>
 *
 * @method null|AstronomicalEntryInterface find(integer $id)
 */
interface AstroEntryRepositoryInterface extends ObjectRepository
{
    public function prototype(): AstronomicalEntryInterface;

    public function getByUserAndSystem(int $userId, ?int $starSystemId): ?AstronomicalEntryInterface;

    public function getByUserAndRegion(int $userId, ?int $regionId): ?AstronomicalEntryInterface;

    public function save(AstronomicalEntryInterface $entry): void;

    public function truncateAllAstroEntries(): void;
}
