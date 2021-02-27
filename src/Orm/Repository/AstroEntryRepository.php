<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AstronomicalEntryInterface;

final class AstroEntryRepository extends EntityRepository implements AstroEntryRepositoryInterface
{
    public function prototype(): AstronomicalEntryInterface
    {
        return new AstronomicalEntry();
    }
}
