<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowIgnoreList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;

final class ShowIgnoreList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_IGNORELIST';

    private IgnoreListRepositoryInterface $ignoreListRepository;

    public function __construct(
        IgnoreListRepositoryInterface $ignoreListRepository
    ) {
        $this->ignoreListRepository = $ignoreListRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setTemplateFile('html/ignorelist.xhtml');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', static::VIEW_IDENTIFIER),
            _('Ignoreliste')
        );
        $game->setPageTitle(_('Ignoreliste'));

        $game->setTemplateVar('IGNORE_LIST', $this->ignoreListRepository->getByUser($userId));
        $game->setTemplateVar('REMOTE_IGNORES', $this->ignoreListRepository->getByRecipient($userId));
    }
}
