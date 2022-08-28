<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

class DatabaseTopActivTradePost extends DatabaseTopList
{
    private $name = null;
    private $transactions = null;

    function __construct($entry)
    {
        parent::__construct($entry['name']);
        $this->name = $entry['name'];
        $this->transactions = $entry['transactions'];
    }

    function getTransactions()
    {
        return $this->transactions;
    }

    function getName()
    {
        return $this->name;
    }
}