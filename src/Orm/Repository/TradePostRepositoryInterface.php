<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradePostInterface;

/**
 * @method null|TradePostInterface find(integer $id)
 */
interface TradePostRepositoryInterface extends ObjectRepository
{
    /**
     * @return TradePostInterface[]
     */
    public function getByUserLicense(int $userId): array;
}