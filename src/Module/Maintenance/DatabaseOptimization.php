<?php

namespace Stu\Module\Maintenance;

use Stu\Lib\DbInterface;

final class DatabaseOptimization implements MaintenanceHandlerInterface
{

    private $db;

    public function __construct(
        DbInterface $db
    ) {
        $this->db = $db;
    }

    public function handle(): void
    {
        $result = array_column(mysqli_fetch_all($this->db->query('SHOW TABLES')), 0);

        foreach ($result as $table_name) {
            $this->db->query("OPTIMIZE TABLE " . $table_name);
        }
    }
}
