<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<AstronomicalEntry>
 *
 * @method null|AstronomicalEntryInterface find(integer $id)
 * @method AstronomicalEntryInterface[] findAll()
 */
interface AstroEntryRepositoryInterface extends ObjectRepository
{
    public function prototype(): AstronomicalEntryInterface;

    public function save(AstronomicalEntryInterface $entry): void;

    public function delete(AstronomicalEntryInterface $entry): void;

    public function truncateAllAstroEntries(): void;

    /**
     * @return array<AstronomicalEntryInterface>
     */
    public function getByUser(UserInterface $user): array;

    /**
     * @return array<AstronomicalEntryInterface>
     */
    public function getByUserAndState(UserInterface $user, int $state): array;
}