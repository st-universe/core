<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Noodlehaus\ConfigInterface;
use Override;
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
    public function __construct(private ConfigInterface $config, private ComponentLoaderInterface $componentLoader)
    {
    }

    #[Override]
    public function render(
        GameControllerInterface $game,
        ?UserInterface $user,
        TwigPageInterface $twigPage
    ): string {

        $this->componentLoader->loadComponentUpdates($game);
        $this->componentLoader->loadRegisteredComponents($twigPage, $game);
        $this->setGameVariables($twigPage, $game);
        $this->setUserVariables($user, $twigPage);

        $twigPage->setVar('WIKI', $this->config->get('wiki.base_url'));
        $twigPage->setVar('FORUM', $this->config->get('board.base_url'));
        $twigPage->setVar('CHAT', $this->config->get('discord.url'));

        return $twigPage->render();
    }

    private function setGameVariables(TwigPageInterface $twigPage, GameControllerInterface $game): void
    {
        $twigPage->setVar('MACRO', $game->getMacro());
        $twigPage->setVar('NAVIGATION', $game->getNavigation());
        $twigPage->setVar('PAGETITLE', $game->getPageTitle());
        $twigPage->setVar('INFORMATION', $game->getInformation());
        $twigPage->setVar('TARGET_LINK', $game->getTargetLink());
        $twigPage->setVar('ACHIEVEMENTS', $game->getAchievements());
        $twigPage->setVar('EXECUTEJSBEFORERENDER', $game->getExecuteJS(GameEnum::JS_EXECUTION_BEFORE_RENDER));
        $twigPage->setVar('EXECUTEJSAFTERRENDER', $game->getExecuteJS(GameEnum::JS_EXECUTION_AFTER_RENDER));
        $twigPage->setVar('EXECUTEJSAJAXUPDATE', $game->getExecuteJS(GameEnum::JS_EXECUTION_AJAX_UPDATE));
        $twigPage->setVar('JAVASCRIPTPATH', $game->getJavascriptPath(), true);
        $twigPage->setVar('ISNPC', $game->isNpc());
        $twigPage->setVar('ISADMIN', $game->isAdmin());
        $twigPage->setVar('BENCHMARK', $game->getBenchmarkResult());
        $twigPage->setVar('GAME_STATS', $game->getGameStats());

        if ($game->hasUser()) {
            $twigPage->setVar('SESSIONSTRING', $game->getSessionString(), true);
        }
    }

    private function setUserVariables(?UserInterface $user, TwigPageInterface $twigPage): void
    {
        if ($user === null) {
            $twigPage->setVar('USER', null);
        } else {
            $twigPage->setVar('USER', new UserContainer(
                $user->getId(),
                $user->getAvatar(),
                $user->getName(),
                $user->getFactionId(),
                $user->getCss(),
                $user->hasStationsNavigation(),
                $user->getDeals()
            ));
        }
    }
}
