<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradePostInterface;

interface TradePostRepositoryInterface extends ObjectRepository
{
    /**
     * @return TradePostInterface[]
     */
    public function getByUserLicense(int $userId): array;
}