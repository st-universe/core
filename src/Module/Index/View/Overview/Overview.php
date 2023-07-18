<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\Overview;

use Noodlehaus\ConfigInterface;
use Stu\Component\Index\News\NewsFactoryInterface;
use Stu\Component\Index\News\NewsItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\NewsInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private NewsRepositoryInterface $newsRepository;

    private NewsFactoryInterface $newsFactory;

    private ConfigInterface $config;

    public function __construct(
        NewsRepositoryInterface $newsRepository,
        NewsFactoryInterface $newsFactory,
        ConfigInterface $config
    ) {
        $this->newsRepository = $newsRepository;
        $this->newsFactory = $newsFactory;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Star Trek Universe'));
        $game->setTemplateFile('html/index.xhtml');

        $game->setTemplateVar(
            'SYSTEM_NEWS',
            array_map(
                fn(NewsInterface $news): NewsItemInterface => $this->newsFactory->createNewsItem(
                    $news
                ),
                $this->newsRepository->getRecent(5)
            )
        );

        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));
    }
}
