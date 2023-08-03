<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
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

    /** @var array<RenderFragmentInterface> */
    private array $renderFragments;

    /**
     * @param array<RenderFragmentInterface> $renderFragments
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
        TwigPageInterface $twigPage
    ): string {

        $this->setGameVariables($twigPage, $game);
        $this->setUserVariables($user, $twigPage);

        $twigPage->setVar('GAME_VERSION', $this->config->get('game.version'));
        $twigPage->setVar('WIKI', $this->config->get('wiki.base_url'));
        $twigPage->setVar('FORUM', $this->config->get('board.base_url'));
        $twigPage->setVar('CHAT', $this->config->get('discord.url'));

        // render fragments are user related, so render them only if a user is available
        if ($user !== null) {
            foreach ($this->renderFragments as $renderFragment) {
                $renderFragment->render($user, $twigPage);
            }
        }

        return $twigPage->render();
    }

    private function setGameVariables(TwigPageInterface $twigPage, GameControllerInterface $game): void
    {
        $twigPage->setVar('NAVIGATION', $game->getNavigation());
        $twigPage->setVar('PAGETITLE', $game->getPageTitle());
        $twigPage->setVar('INFORMATION', $game->getInformation());
        $twigPage->setVar('ACHIEVEMENTS', $game->getAchievements());
        $twigPage->setVar('EXECUTEJS', $game->getExecuteJS());
        $twigPage->setVar('EXECUTEJSAFTERRENDER', $game->getExecuteJsAfterRender());
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
                $user->getAvatar(),
                $user->getName(),
                $user->getFactionId(),
                $user->getCss(),
                $user->getPrestige(),
                $user->hasStationsNavigation()
            ));
        }
    }
}
