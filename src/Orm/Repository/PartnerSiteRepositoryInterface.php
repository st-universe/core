<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PartnerSite;

/**
 * @extends ObjectRepository<PartnerSite>
 *
 * @method null|PartnerSite find(integer $id)
 */
interface PartnerSiteRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<PartnerSite>
     */
    public function getOrdered(): iterable;
}
