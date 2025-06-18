<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\PirateRound;
use Stu\Orm\Entity\PirateRoundInterface;

/**
 * @extends EntityRepository<PirateRound>
 */
final class PirateRoundRepository extends EntityRepository implements PirateRoundRepositoryInterface
{
    #[Override]
    public function prototype(): PirateRoundInterface
    {
    #[Override]
    public function getCurrentActiveRound(): ?PirateRoundInterface
    {
        return $this->findOneBy(['end_time' => null]);
    }

    #[Override]
    public function findActiveRounds(): array
    {
        return $this->findBy(
            ['end_time' => null],
            ['start' => 'desc']
        );
    }
}
