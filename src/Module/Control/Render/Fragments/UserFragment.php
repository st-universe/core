<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Renders the user box in the header
 */
final class UserFragment implements RenderFragmentInterface
{
    public function render(
        UserInterface $user,
        TalPageInterface|TwigPageInterface $page,
        GameControllerInterface $game
    ): void {
        $page->setVar('PRESTIGE', $user->getPrestige());
    }
}
