<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

interface DatabaseCategoryRepositoryInterface extends ObjectRepository
{
    public function getByTypeId(int $type_id): array;
}