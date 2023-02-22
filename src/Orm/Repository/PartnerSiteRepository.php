<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PartnerSite;

/**
 * @extends EntityRepository<PartnerSite>
 */
final class PartnerSiteRepository extends EntityRepository implements PartnerSiteRepositoryInterface
{
    public function getOrdered(): iterable
    {
        return $this->findBy(
            [],
            ['id' => 'asc']
        );
    }
}
