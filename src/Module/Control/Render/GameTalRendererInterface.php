<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\UserInterface;

interface GameTalRendererInterface
{
    /**
     * Returns the html code of the renderer site template
     */
    public function render(
        GameControllerInterface $game,
        ?UserInterface $user,
        TalPageInterface $talPage
    ): string;
}
