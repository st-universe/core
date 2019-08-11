<?php

declare(strict_types=1);

abstract class DatabaseTopList
{

    private $user_id = null;

    function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getUser()
    {
        return ResourceCache()->getUser($this->getUserId());
    }
}