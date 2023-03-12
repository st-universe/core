<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopActivTradePost extends DatabaseTopList
{
    /** @var array{id: int, name: string, transactions: int} */
    private array $entry;

    /**
     * @param array{id: int, name: string, transactions: int} $entry
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        array $entry
    ) {
        parent::__construct(
            $userRepository,
            $entry['id']
        );
        $this->entry = $entry;
    }

    public function getTransactions(): int
    {
        return $this->entry['transactions'];
    }

    public function getName(): string
    {
        return $this->entry['name'];
    }
}