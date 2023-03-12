<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PartnerSite;
use Stu\Orm\Entity\PartnerSiteInterface;

/**
 * @extends ObjectRepository<PartnerSite>
 *
 * @method null|PartnerSiteInterface find(integer $id)
 */
interface PartnerSiteRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<PartnerSiteInterface>
     */
    public function getOrdered(): iterable;
}
