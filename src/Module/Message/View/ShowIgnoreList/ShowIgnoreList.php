<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowIgnoreList;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;

final class ShowIgnoreList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_IGNORELIST';

    public function __construct(private IgnoreListRepositoryInterface $ignoreListRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setViewTemplate('html/communication/ignorelist.twig');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', self::VIEW_IDENTIFIER),
            _('Ignoreliste')
        );
        $game->setPageTitle(_('Ignoreliste'));

        $game->setTemplateVar('IGNORE_LIST', $this->ignoreListRepository->getByUser($userId));
        $game->setTemplateVar('REMOTE_IGNORES', $this->ignoreListRepository->getByRecipient($userId));
    }
}
