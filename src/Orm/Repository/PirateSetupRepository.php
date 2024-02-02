<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateSetup;
use Stu\Orm\Entity\PirateSetupInterface;

/**
 * @extends EntityRepository<PirateSetup>
 * 
 * @method PirateSetupInterface[] findAll()
 */
final class PirateSetupRepository extends EntityRepository implements PirateSetupRepositoryInterface
{
}
