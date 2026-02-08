<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyClassDeposit;

/**
 * @extends EntityRepository<ColonyClassDeposit>
 */
final class ColonyClassDepositRepository extends EntityRepository implements ColonyClassDepositRepositoryInterface {}
