<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\Game\GameEnum;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Executes the render chain for the site template
 *
 * Also registers a set of default variables for rendering
 */
final class GameTwigRenderer implements GameTwigRendererInterface
{
    private const string GAME_VERSION_DEV = 'dev';

    public function __construct(
        private TwigPageInterface $twigPage,
        private ConfigInterface $config,
        private StuConfigInterface $stuConfig
    ) {}

    #[Override]
    public function render(
        GameControllerInterface $game,
        ?UserInterface $user
    ): string {

        $this->setGameVariables($game);
        $this->setUserVariables($user);

        $this->twigPage->setVar('WIKI', $this->config->get('wiki.base_url'));
        $this->twigPage->setVar('FORUM', $this->config->get('board.base_url'));
        $this->twigPage->setVar('CHAT', $this->config->get('discord.url'));

        return $this->twigPage->render();
    }

    private function setGameVariables(GameControllerInterface $game): void
    {
        $this->twigPage->setVar('MACRO', $game->getMacro());
        $this->twigPage->setVar('NAVIGATION', $game->getNavigation());
        $this->twigPage->setVar('PAGETITLE', $game->getPageTitle());
        $this->twigPage->setVar('INFORMATION', $game->getInformation());
        $this->twigPage->setVar('TARGET_LINK', $game->getTargetLink());
        $this->twigPage->setVar('ACHIEVEMENTS', $game->getAchievements());
        $this->twigPage->setVar('EXECUTEJSBEFORERENDER', $game->getExecuteJS(GameEnum::JS_EXECUTION_BEFORE_RENDER));
        $this->twigPage->setVar('EXECUTEJSAFTERRENDER', $game->getExecuteJS(GameEnum::JS_EXECUTION_AFTER_RENDER));
        $this->twigPage->setVar('EXECUTEJSAJAXUPDATE', $game->getExecuteJS(GameEnum::JS_EXECUTION_AJAX_UPDATE));
        $this->twigPage->setVar('JAVASCRIPTPATH', $this->getJavascriptPath(), true);
        $this->twigPage->setVar('IS_NPC', $game->isNpc());
        $this->twigPage->setVar('IS_ADMIN', $game->isAdmin());
        $this->twigPage->setVar('BENCHMARK', $game->getBenchmarkResult());
        $this->twigPage->setVar('GAME_STATS', $game->getGameStats());

        if ($game->hasUser()) {
            $this->twigPage->setVar('SESSIONSTRING', $game->getSessionString(), true);
        }
    }

    private function setUserVariables(?UserInterface $user): void
    {
        if ($user === null) {
            $this->twigPage->setVar('USER', null);
        } else {
            $this->twigPage->setVar('USER', new UserContainer(
                $user->getId(),
                $user->getAvatar(),
                $user->getName(),
                $user->getFactionId(),
                $user->getCss(),
                $user->hasStationsNavigation()
            ));
        }
    }

    private function getJavascriptPath(): string
    {
        $gameVersion = $this->stuConfig->getGameSettings()->getVersion();
        if ($gameVersion === self::GAME_VERSION_DEV) {
            return '/static';
        }

        return sprintf(
            '/version_%s/static',
            $gameVersion
        );
    }
}
