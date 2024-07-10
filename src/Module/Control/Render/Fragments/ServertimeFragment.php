<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Renders the user box in the header
 */
final class ServertimeFragment implements RenderFragmentInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    #[Override]
    public function render(
        UserInterface $user,
        TwigPageInterface $page,
        GameControllerInterface $game
    ): void {
        $page->setVar('GAMETURN', $game->getCurrentRound()->getTurn());
        $page->setVar('GAME_VERSION', $this->config->get('game.version'));
    }
}
