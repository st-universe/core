<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowPrestigeLog;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PrestigeLogRepositoryInterface;

final class ShowPrestigeLog implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PRESTIGELOG';

    private const int DEFAULT_LIMIT = 50;

    public function __construct(private PrestigeLogRepositoryInterface $prestigeLogRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $count = request::postInt('count');

        if (!$count || $count < 1) {
            $count = self::DEFAULT_LIMIT;
        }

        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('Prestigehistorie')
        );
        $game->setPageTitle(_('/ Datenbank / Prestigehistorie'));
        $game->setViewTemplate('html/database/prestigeLog.twig');

        $game->setTemplateVar('USER_PRESTIGE', $game->getUser()->getPrestige());
        $game->setTemplateVar('COUNT', $count);
        $game->setTemplateVar('LOGS', $this->prestigeLogRepository->getPrestigeHistory($game->getUser(), $count));
    }
}
