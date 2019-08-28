<?php

class ContactlistWrapper
{
    private $user;

    public function __construct(
        UserData $user
    ) {
        $this->user = $user;
    }

    public function __get($userId)
    {
        return Contactlist::hasContact($this->user->getId(), $userId);
    }

}
