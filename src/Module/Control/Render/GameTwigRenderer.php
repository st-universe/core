<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\Database\AchievementManagerInterface;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Component\Player\UserAwardEnum;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class GameTwigRenderer implements GameTwigRendererInterface
{
    private const string GAME_VERSION_DEV = 'dev';

    public function __construct(
        private readonly TwigPageInterface $twigPage,
        private readonly ConfigInterface $config,
        private readonly StuConfigInterface $stuConfig,
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly JavascriptExecutionInterface $javascriptExecution,
        private readonly AchievementManagerInterface $achievementManager
    ) {}

    #[Override]
    public function render(
        GameControllerInterface $game,
        ?User $user
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
        $this->twigPage->setVar('ACHIEVEMENTS', $this->achievementManager->getAchievements());
        $this->twigPage->setVar('EXECUTEJSBEFORERENDER', $this->javascriptExecution->getExecuteJS(JavascriptExecutionTypeEnum::BEFORE_RENDER));
        $this->twigPage->setVar('EXECUTEJSAFTERRENDER', $this->javascriptExecution->getExecuteJS(JavascriptExecutionTypeEnum::AFTER_RENDER));
        $this->twigPage->setVar('EXECUTEJSAJAXUPDATE', $this->javascriptExecution->getExecuteJS(JavascriptExecutionTypeEnum::ON_AJAX_UPDATE));
        $this->twigPage->setVar('JAVASCRIPTPATH', $this->getJavascriptPath(), true);
        $this->twigPage->setVar('IS_NPC', $game->isNpc());
        $this->twigPage->setVar('IS_ADMIN', $game->isAdmin());
        $this->twigPage->setVar('BENCHMARK', $game->getBenchmarkResult());
        $this->twigPage->setVar('GAME_STATS', $game->getGameStats());

        if ($game->hasUser()) {
            $this->twigPage->setVar('SESSIONSTRING', $game->getSessionString(), true);
        }
    }

    private function setUserVariables(?User $user): void
    {
        if ($user === null) {
            $this->twigPage->setVar('USER', null);
        } else {
            $this->twigPage->setVar('USER', new UserContainer(
                $user->getId(),
                $this->userSettingsProvider->getAvatar($user),
                $user->getName(),
                $user->getFactionId(),
                $this->userSettingsProvider->getCss($user)->value,
                $this->hasStationsNavigation($user)
            ));
        }
    }

    private function hasStationsNavigation(User $user): bool
    {
        if ($user->isNpc()) {
            return true;
        }

        if ($user->hasAward(UserAwardEnum::RESEARCHED_STATIONS)) {
            return true;
        }

        return $this->crewAssignmentRepository->hasCrewOnForeignStation($user);
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
