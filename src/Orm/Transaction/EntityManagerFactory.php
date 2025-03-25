<?php

namespace Stu\Orm\Transaction;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerFactory implements EntityManagerFactoryInterface
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
        private Configuration $configuration
    ) {}

    public function createEntityManager(?Connection $connection = null): EntityManagerInterface
    {
        return new EntityManager(
            $connection ?? $this->connectionFactory->createConnection(),
            $this->configuration
        );
    }
}
