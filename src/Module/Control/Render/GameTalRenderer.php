<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Noodlehaus\ConfigInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Executes the render chain for the site template
 *
 * Also registers a set of default variables for rendering
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
        ?UserInterface $user,
        TalPageInterface $talPage
    ): string {
        $talPage->setVar('THIS', $game);
        $talPage->setVar('EXECUTEJSBEFORERENDER', $game->getExecuteJS(GameEnum::JS_EXECUTION_BEFORE_RENDER));
        $talPage->setVar('USER', $user);
        $talPage->setVar('GAME_VERSION', $this->config->get('game.version'));
        $talPage->setVar('WIKI', $this->config->get('wiki.base_url'));
        $talPage->setVar('FORUM', $this->config->get('board.base_url'));
        $talPage->setVar('CHAT', $this->config->get('discord.url'));
        $talPage->setVar(
            'ASSET_PATHS',
            [
                'alliance' => $this->config->get('game.alliance_avatar_path'),
                'user' => $this->config->get('game.user_avatar_path'),
                'faction' => 'assets/rassen/',
            ]
        );

        // render fragments are user related, so render them only if a user is available
        if ($user !== null) {
            foreach ($this->renderFragments as $renderFragment) {
                $renderFragment->render($user, $talPage, $game);
            }
        }

        return $talPage->parse();
    }
}
