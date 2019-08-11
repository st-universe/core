<?php

declare(strict_types=1);

namespace Stu\Lib;

interface DbInterface
{
    public function query(string $qry, int $mode = 0);

    public function getQueryCount(): int;

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;

    public function freeResult($result_id);
}