<?php

namespace Stu\Orm\Transaction;

use Doctrine\DBAL\Connection;

interface ConnectionFactoryInterface
{
    public function createConnection(): Connection;
}
