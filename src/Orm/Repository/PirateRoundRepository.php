<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateRound;

/**
 * @extends EntityRepository<PirateRound>
 */
final class PirateRoundRepository extends EntityRepository implements PirateRoundRepositoryInterface
{
    #[\Override]
    public function prototype(): PirateRound
    {
        return new PirateRound();
    }

    #[\Override]
    public function save(PirateRound $pirateRound): void
    {
        $em = $this->getEntityManager();

        $em->persist($pirateRound);
    }

    #[\Override]
    public function delete(PirateRound $pirateRound): void
    {
        $em = $this->getEntityManager();

        $em->remove($pirateRound);
    }

    #[\Override]
    public function getCurrentActiveRound(): ?PirateRound
    {
        return $this->findOneBy(['end_time' => null]);
    }

    #[\Override]
    public function findActiveRounds(): array
    {
        return $this->findBy(
            ['end_time' => null],
            ['start' => 'desc']
        );
    }
}
