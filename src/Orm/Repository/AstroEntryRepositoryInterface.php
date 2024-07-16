<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @extends ObjectRepository<AstronomicalEntry>
 *
 * @method null|AstronomicalEntryInterface find(integer $id)
 * @method AstronomicalEntryInterface[] findAll()
 */
interface AstroEntryRepositoryInterface extends ObjectRepository
{
    public function prototype(): AstronomicalEntryInterface;

    public function getByShipLocation(ShipInterface $ship, bool $showOverSystem = true): ?AstronomicalEntryInterface;

    public function save(AstronomicalEntryInterface $entry): void;

    public function truncateAllAstroEntries(): void;
}
