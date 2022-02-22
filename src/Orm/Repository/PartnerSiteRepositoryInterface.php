<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PartnerSiteInterface;

/**
 * @method null|PartnerSiteInterface find(integer $id)
 */
interface PartnerSiteRepositoryInterface extends ObjectRepository
{
    /**
     * @return PartnerSiteInterface[]
     */
    public function getOrdered(): iterable;
}
