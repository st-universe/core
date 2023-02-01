<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\TalPageInterface;

/**
 * Executes the render chain for the site template
 */
final class GameTalRenderer implements GameTalRendererInterface
{
    private ConfigInterface $config;

    /** @var array<Fragments\RenderFragmentInterface> */
    private array $renderFragments;

    /**
     * @param array<Fragments\RenderFragmentInterface> $renderFragments
     */
    public function __construct(
        ConfigInterface $config,
        array $renderFragments
    ) {
        $this->config = $config;
        $this->renderFragments = $renderFragments;
    }

    public function render(
        GameControllerInterface $game,
        TalPageInterface $talPage
    ): string {
        $user = $game->getUser();

        $talPage->setVar('THIS', $game);
        $talPage->setVar('USER', $user);
        $talPage->setVar('GAME_VERSION', $this->config->get('game.version'));
        $talPage->setVar('WIKI', $this->config->get('wiki.base_url'));
        $talPage->setVar('FORUM', $this->config->get('board.base_url'));
        $talPage->setVar('CHAT', $this->config->get('discord.url'));

        // render fragments are user related, so render them only if a user is available
        if ($user !== null) {
            foreach ($this->renderFragments as $renderFragment) {
                $renderFragment->render($user, $talPage);
            }
        }

        return $talPage->parse();
    }
}