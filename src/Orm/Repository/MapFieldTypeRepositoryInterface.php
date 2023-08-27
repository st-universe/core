<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\MapFieldTypeInterface;

/**
 * @extends ObjectRepository<MapFieldType>
 */
interface MapFieldTypeRepositoryInterface extends ObjectRepository
{

    public function save(MapFieldTypeInterface $map): void;
}
