<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PirateRound;
use Stu\Orm\Entity\PirateRoundInterface;

/**
 * @extends ObjectRepository<PirateRound>
 *
 * @method null|PirateRoundInterface find(integer $id)
 * @method PirateRoundInterface[] findAll()
 */
interface PirateRoundRepositoryInterface extends ObjectRepository
{
    public function prototype(): PirateRoundInterface;

    public function save(PirateRoundInterface $pirateRound): void;

    public function delete(PirateRoundInterface $pirateRound): void;

    public function getCurrentActiveRound(): ?PirateRoundInterface;

    /**
     * @return PirateRoundInterface[]
     */
    public function findActiveRounds(): array;
}
