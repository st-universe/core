<?php

declare(strict_types=1);

use Stu\Orm\Repository\UserRepositoryInterface;

abstract class DatabaseTopList
{

    private $user_id = null;

    function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getUser()
    {
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->user_id);
    }
}