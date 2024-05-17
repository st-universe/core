<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\PirateWrathInterface;

/**
 * @extends ObjectRepository<PirateWrath>
 *
 * @method PirateWrathInterface[] findAll()
 */
interface PirateWrathRepositoryInterface extends ObjectRepository
{
    public function save(PirateWrathInterface $wrath): void;

    public function delete(PirateWrathInterface $wrath): void;

    public function prototype(): PirateWrathInterface;

    public function truncateAllEntries(): void;

    /**
     * @return PirateWrathInterface[]
     */
    public function getPirateWrathTop10(): array;
}
