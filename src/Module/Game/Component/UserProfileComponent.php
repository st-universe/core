<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;

/**
 * Renders the user box in the header
 */
final class UserProfileComponent implements ComponentInterface
{
    private const int NEW_USER_DURATION = 7 * 24 * 60 * 60;

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->setTemplateVar('PRESTIGE', $user->getPrestige());
        $game->setTemplateVar(
            'IS_NEW_USER',
            $user->getRegistration()->getCreationDate() > time() - self::NEW_USER_DURATION
        );
    }
}
