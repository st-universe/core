<?php

namespace Stu\Module\Maintenance;

use Noodlehaus\ConfigInterface;

final class DatabaseBackup implements MaintenanceHandlerInterface
{
    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function handle(): void
    {
        $this->cleanup();
        $this->backup();
    }

    private function cleanup(): void
    {
        $backup_dir = $this->config->get('db.backup_dir');

        $dir = dir($backup_dir);
        while ($file = $dir->read()) {
            $filename = sprintf('%s/%s', $backup_dir, $file);

            if (is_file($filename) && filectime($filename) < time() - (int)$this->config->get('db.backup_cycle_time')) {
                unlink($filename);
            }
        }
        $dir->close();
    }

    private function backup(): void
    {
        $cmd = sprintf(
            'PGPASSWORD="%s" pg_dump -Fc -U %s -h %s %s > %s/%s.dump',
            $this->config->get('db.pass'),
            $this->config->get('db.user'),
            $this->config->get('db.host'),
            $this->config->get('db.database'),
            $this->config->get('db.backup_dir'),
            date("d-m-Y", time())
        );

        system($cmd);
    }
}
