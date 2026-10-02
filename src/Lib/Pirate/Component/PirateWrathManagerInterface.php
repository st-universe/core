<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Component;

use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\User;

interface PirateWrathManagerInterface
{
    public function increaseWrathViaTrigger(User $user, PirateReactionTriggerEnum $reactionTrigger): void;

    public function increaseWrath(User $user, int $amount): void;

    public function decreaseWrath(User $user, int $amount): void;

    public function setProtectionTimeoutFromPrestige(User $user, int $prestige, ActionControllerContext $context): void;
}
