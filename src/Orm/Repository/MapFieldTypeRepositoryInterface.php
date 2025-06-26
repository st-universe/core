<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapFieldType;

/**
 * @extends ObjectRepository<MapFieldType>
 *
 * @method null|MapFieldType find(integer $id)
 * @method MapFieldType[] findAll()
 */
interface MapFieldTypeRepositoryInterface extends ObjectRepository
{
    public function save(MapFieldType $map): void;
}
