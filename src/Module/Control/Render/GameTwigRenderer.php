<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Noodlehaus\ConfigInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Executes the render chain for the site template
 *
 * Also registers a set of default variables for rendering
 */
final class GameTwigRenderer implements GameTwigRendererInterface
{
    private ConfigInterface $config;

    private ComponentLoaderInterface $componentLoader;

    public function __construct(
        ConfigInterface $config,
        ComponentLoaderInterface $componentLoader
    ) {
        $this->config = $config;
        $this->componentLoader = $componentLoader;
    }

    public function render(
        GameControllerInterface $game,
        ?UserInterface $user,
        TwigPageInterface $twigPage
    ): string {

        $this->componentLoader->loadComponentUpdates($game);
        $this->componentLoader->loadRegisteredComponents($twigPage, $game);
        $this->setGameVariables($twigPage, $game);
        $this->setUserVariables($user, $twigPage);

        $twigPage->setVar('GAME_VERSION', $this->config->get('game.version'));
        $twigPage->setVar('WIKI', $this->config->get('wiki.base_url'));
        $twigPage->setVar('FORUM', $this->config->get('board.base_url'));
        $twigPage->setVar('CHAT', $this->config->get('discord.url'));

        return $twigPage->render();
    }

    private function setGameVariables(TwigPageInterface $twigPage, GameControllerInterface $game): void
    {
        $twigPage->setVar('NAVIGATION', $game->getNavigation());
        $twigPage->setVar('PAGETITLE', $game->getPageTitle());
        $twigPage->setVar('INFORMATION', $game->getInformation());
        $twigPage->setVar('ACHIEVEMENTS', $game->getAchievements());
        $twigPage->setVar('EXECUTEJSBEFORERENDER', $game->getExecuteJS(GameEnum::JS_EXECUTION_BEFORE_RENDER));
        $twigPage->setVar('EXECUTEJSAFTERRENDER', $game->getExecuteJS(GameEnum::JS_EXECUTION_AFTER_RENDER));
        $twigPage->setVar('EXECUTEJSAJAXUPDATE', $game->getExecuteJS(GameEnum::JS_EXECUTION_AJAX_UPDATE));
        $twigPage->setVar('SESSIONSTRING', $game->getSessionString(), true);
        $twigPage->setVar('JAVASCRIPTPATH', $game->getJavascriptPath(), true);
        $twigPage->setVar('ISADMIN', $game->isAdmin());
        $twigPage->setVar('GAMETURN', $game->getCurrentRound()->getTurn());
        $twigPage->setVar('BENCHMARK', $game->getBenchmarkResult());
    }

    private function setUserVariables(?UserInterface $user, TwigPageInterface $twigPage): void
    {
        if ($user === null) {
            $twigPage->setVar('USER', null);
        } else {
            $twigPage->setVar('USER', new UserContainer(
                $user->getId(),
                $user->getCss(),
                $user->hasStationsNavigation(),
                $user->getDeals()
            ));
        }
    }
}
