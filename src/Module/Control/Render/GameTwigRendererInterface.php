<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

interface GameTwigRendererInterface
{
    /**
     * Returns the html code of the renderer site template
     */
    public function render(
        GameControllerInterface $game,
        ?UserInterface $user,
        TwigPageInterface $twigPage
    ): string;
}
