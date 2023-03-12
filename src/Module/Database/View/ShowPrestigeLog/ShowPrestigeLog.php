<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowPrestigeLog;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PrestigeLogRepositoryInterface;

final class ShowPrestigeLog implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PRESTIGELOG';

    private const DEFAULT_LIMIT = 50;

    private PrestigeLogRepositoryInterface $prestigeLogRepository;

    public function __construct(
        PrestigeLogRepositoryInterface $prestigeLogRepository
    ) {
        $this->prestigeLogRepository = $prestigeLogRepository;
    }

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
                static::VIEW_IDENTIFIER
            ),
            _('Prestigehistorie')
        );
        $game->setPageTitle(_('/ Datenbank / Prestigehistorie'));
        $game->showMacro('html/database.xhtml/prestige_log');

        $game->setTemplateVar('COUNT', $count);
        $game->setTemplateVar('LOGS', $this->prestigeLogRepository->getPrestigeHistory($game->getUser(), $count));
    }
}
