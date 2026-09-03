<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PirateSetup;

/**
 * @extends ObjectRepository<PirateSetup>
 */
interface PirateSetupRepositoryInterface extends ObjectRepository
{
    /** @return array<PirateSetup> */
    public function getAllOrderedByName(): array;
}
