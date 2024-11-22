<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListFlights extends DatabaseTopList
{
    /** @var array{user_id: int, sc: int, factionid: null|int, shipc: int} */
    private array $entry;

    /**
     * @param array{user_id: int, sc: int, factionid: null|int, shipc: int} $entry
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        array $entry
    ) {
        parent::__construct(
            $userRepository,
            $entry['user_id']
        );
        $this->entry = $entry;
    }

    public function getSignatures(): int
    {
        return $this->entry['sc'];
    }

    public function getShipCount(): int
    {
        return $this->entry['shipc'];
    }

    public function getFaction(): ?int
    {
        return $this->entry['factionid'];
    }
}
