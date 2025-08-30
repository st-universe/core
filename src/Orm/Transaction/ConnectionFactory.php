<?php

namespace Stu\Orm\Transaction;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Tools\DsnParser;
use Noodlehaus\ConfigInterface;
use Stu\Component\Logging\Sql\SqlLogger;
use Stu\Module\Config\StuConfigInterface;

class ConnectionFactory implements ConnectionFactoryInterface
{
    public function __construct(
        private ConfigInterface $config,
        private StuConfigInterface $stuConfig,
        private SqlLogger $sqlLogger
    ) {}

    public function createConnection(): Connection
    {
        $configuration = null;
        if ($this->stuConfig->getDebugSettings()->getSqlLoggingSettings()->isActive()) {
            $configuration = new Configuration();
            $configuration->setMiddlewares([new Middleware($this->sqlLogger)]);
        }

        //use sqlite database
        if ($this->stuConfig->getDbSettings()->useSqlite()) {
            $dsnParser = new DsnParser(['sqlite' => 'pdo_sqlite']);
            $connectionParams = $dsnParser
                ->parse($this->stuConfig->getDbSettings()->getSqliteDsn());

            return DriverManager::getConnection($connectionParams, $configuration);
        }

        return DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'user' => $this->config->get('db.user'),
            'password' => $this->config->get('db.pass'),
            'dbname' => $this->config->get('db.database'),
            'host'  => $this->config->get('db.host'),
            'charset' => 'utf8',
        ], $configuration);
    }
}
