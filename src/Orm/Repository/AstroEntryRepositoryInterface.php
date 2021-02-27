<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AstronomicalEntryInterface;

/**
 * @method null|AstronomicalEntryInterface find(integer $id)
 */
interface AstroEntryRepositoryInterface extends ObjectRepository
{
    public function prototype(): AstronomicalEntryInterface;

    public function getByUserAndSystem($userId, $starSystemId): ?AstronomicalEntryInterface;

    public function save(AstronomicalEntryInterface $entry): void;
}
