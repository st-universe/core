<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Proxy\__CG__\Stu\Orm\Entity\AstronomicalEntry;

/**
 * @extends ObjectRepository<AstronomicalEntry>
 *
 * @method null|AstronomicalEntryInterface find(integer $id)
 */
interface AstroEntryRepositoryInterface extends ObjectRepository
{
    public function prototype(): AstronomicalEntryInterface;

    public function getByUserAndSystem($userId, $starSystemId): ?AstronomicalEntryInterface;

    public function save(AstronomicalEntryInterface $entry): void;
}
