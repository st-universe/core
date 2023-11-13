<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\View\Overview;

use Noodlehaus\ConfigInterface;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Index\News\NewsFactoryInterface;
use Stu\Component\Index\News\NewsItemInterface;
use Stu\Component\Player\UserCssEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\NewsInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private ConfigInterface $config;

    private NewsRepositoryInterface $newsRepository;

    private NewsFactoryInterface $newsFactory;

    public function __construct(
        ConfigInterface $config,
        NewsRepositoryInterface $newsRepository,
        NewsFactoryInterface $newsFactory
    ) {
        $this->config = $config;
        $this->newsRepository = $newsRepository;
        $this->newsFactory = $newsFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user === null) {
            $game->setPageTitle(_('Star Trek Universe'));
            $game->setTemplateFile('html/index.xhtml');

            $game->setTemplateVar(
                'SYSTEM_NEWS',
                array_map(
                    fn (NewsInterface $news): NewsItemInterface => $this->newsFactory->createNewsItem(
                        $news
                    ),
                    $this->newsRepository->getRecent(5)
                )
            );

            return;
        }

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );
        $game->setPageTitle(_('/ Optionen'));
        $game->setTemplateFile('html/options.xhtml');

        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));
        $game->setTemplateVar('STARTPAGE', $user->getStartPage());
        $game->setTemplateVar('STARTPAGE_VALUES', ModuleViewEnum::MODULE_VIEW_ARRAY);
        $game->setTemplateVar('RPGBEHAVIOR', $user->getRpgBehavior());
        $game->setTemplateVar('RPG_BEHAVIOR_VALUES', UserRpgBehaviorEnum::cases());
        $game->setTemplateVar('CSSSTYLE', $user->getCss());
        $game->setTemplateVar('CSS_VALUES', UserCssEnum::CSS_CLASS);
    }
}
