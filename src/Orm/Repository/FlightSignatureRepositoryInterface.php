<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\FlightSignatureInterface;

/**
 * @method null|FlightSignatureInterface find(integer $id)
 */
interface FlightSignatureRepositoryInterface extends ObjectRepository
{
    public function prototype(): FlightSignatureInterface;

    public function saveAll(array $array): void;

    public function deleteOldSignatures(int $threshold): void;
}
