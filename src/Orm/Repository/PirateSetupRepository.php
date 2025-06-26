<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateSetup;

/**
 * @extends EntityRepository<PirateSetup>
 *
 * @method PirateSetup[] findAll()
 */
final class PirateSetupRepository extends EntityRepository implements PirateSetupRepositoryInterface
{
}
