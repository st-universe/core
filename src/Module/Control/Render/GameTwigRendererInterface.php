<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\User;

interface GameTwigRendererInterface
{
    /**
     * Returns the html code of the renderer site template
     */
    public function render(
        GameControllerInterface $game,
        ?User $user
    ): string;
}
