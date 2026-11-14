<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Stu\Orm\Entity\User;

trait EntityOwnershipTransferTrait
{
    public function transferToUser(User $user): self
    {
        $this->setUser($user);
        $this->user_id = $user->getId();
        return $this;
    }
}
