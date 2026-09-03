<?php

declare(strict_types=1);

namespace Stu\Orm\Transaction;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerFactoryInterface
{
    public function createEntityManager(?Connection $connection = null): EntityManagerInterface;
}
