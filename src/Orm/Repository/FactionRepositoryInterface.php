<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\FactionInterface;

interface FactionRepositoryInterface extends ObjectRepository
{
    /**
     * @return FactionInterface[]
     */
    public function getByChooseable(bool $chooseable): array;
}