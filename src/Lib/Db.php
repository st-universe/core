<?php

namespace Stu\Lib;

use DBException;
use Noodlehaus\ConfigInterface;

final class Db implements DbInterface
{
    private $config;

    private $dblink;

    private $querycount = 0;

    public function __construct(
        ConfigInterface $config
    )
    {
        $this->config = $config;
    }

    private function getConnection()
    {
        if ($this->dblink === null) {
            $this->dblink = mysqli_connect(
                $this->config->get('db.host'),
                $this->config->get('db.user'),
                $this->config->get('db.pass'),
                $this->config->get('db.database')
            );
            if (!$this->dblink) {
                throw new DBException(mysqli_error($this->dblink));
            }
            mysqli_query($this->dblink, "SET SESSION sql_mode = ''");
            mysqli_set_charset($this->dblink, 'utf8');
            if (mysqli_error($this->dblink)) {
                throw new DBException(mysqli_error($this->dblink));
            }
        }

        return $this->dblink;
    }

    public function query(string $qry, int $mode = 0)
    {
        $this->querycount++;

        $connection = $this->getConnection();

        $result = mysqli_query($connection, $qry);
        if (mysqli_error($connection)) {
            throw new DBException(mysqli_error($connection), $qry);
        }
        if ($mode == 0) {
            return $result;
        }
        if ($mode == 5) {
            return @mysqli_insert_id($connection);
        }
        if ($mode == 6) {
            return @mysqli_affected_rows($connection);
        }
        if (@mysqli_num_rows($result) == 0) {
            return 0;
        }
        if ($mode == 1) {
            $res = current(mysqli_fetch_assoc($result));
            if (!$res) {
                return 0;
            }
            return $res;
        }
        if ($mode == 3) {
            return @mysqli_num_rows($result);
        }
        if ($mode == 4) {
            return @mysqli_fetch_assoc($result);
        }
    }

    public function getQueryCount(): int
    {
        return $this->querycount;
    }

    public function beginTransaction(): void
    {
        $this->query("BEGIN");
    }

    public function commitTransaction(): void
    {
        $this->query("COMMIT");
    }

    public function rollbackTransaction(): void
    {
        $this->query("ROLLBACK");
    }

    public function freeResult($result_id): void
    {
        mysqli_free_result($result_id);
    }

}
