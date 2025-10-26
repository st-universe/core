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
    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $game->setTemplateVar('PRESTIGE', $game->getUser()->getPrestige());
    }
}
