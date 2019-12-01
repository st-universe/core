<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\Overview;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private NewsRepositoryInterface $newsRepository;

    public function __construct(
        NewsRepositoryInterface $newsRepository
    ) {
        $this->newsRepository = $newsRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Star Trek Universe'));
        $game->setTemplateFile('html/index.xhtml');

        $game->setTemplateVar('SYSTEM_NEWS', $this->newsRepository->getRecent(5));
    }
}
