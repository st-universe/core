<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

use Stu\Orm\Entity\FlightSignatureInterface;

interface FlightSignatureRepositoryInterface extends EntityRepository
{
    public function prototype(): FlightSignatureInterface;

    public function saveAll(array $array): void;

    public function deleteOldSignatures(int $threshold): void;
}
