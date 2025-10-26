<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;

/**
 * Renders the colony list in the header
 */
final class ColoniesComponent implements ComponentInterface
{
    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->setTemplateVar(
            'USER_COLONIES',
            ($user->getId() === UserConstants::USER_NOONE) ? [] : $user->getColonies()
        );
    }
}
