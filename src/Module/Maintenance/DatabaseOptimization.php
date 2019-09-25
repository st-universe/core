<?php

namespace Stu\Module\Maintenance;

use Doctrine\ORM\EntityManagerInterface;

final class DatabaseOptimization implements MaintenanceHandlerInterface
{

    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function handle(): void
    {
        $connection = $this->entityManager->getConnection();
        $tableList = $connection->getSchemaManager()->listTables();

        foreach ($tableList as $table_name) {
            $connection->query(
                sprintf(
                    'OPTIMIZE TABLE %s',
                    $table_name->getName()
                )
            );
        }
    }
}
