<?php

namespace Stu\Lib;

use Stu\Orm\Repository\ContactRepositoryInterface;
use UserData;

class ContactlistWrapper
{
    private $user;

    public function __construct(
        UserData $user
    ) {
        $this->user = $user;
    }

    public function __get($opponentId)
    {
        // @todo refactor
        global $container;

        return $container->get(ContactRepositoryInterface::class)->getByUserAndOpponent(
            $this->user->getId(),
            (int) $opponentId
        );
    }

}
