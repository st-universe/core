<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PirateRound;

/**
 * @extends ObjectRepository<PirateRound>
 *
 * @method null|PirateRound find(integer $id)
 * @method PirateRound[] findAll()
 */
interface PirateRoundRepositoryInterface extends ObjectRepository
{
    public function prototype(): PirateRound;

    public function save(PirateRound $pirateRound): void;

    public function delete(PirateRound $pirateRound): void;

    public function getCurrentActiveRound(): ?PirateRound;

    /**
     * @return PirateRound[]
     */
    public function findActiveRounds(): array;
}
