<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Psr\Cache\CacheItemPoolInterface;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class ResetCaches implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_RESET_CACHES';

    private CacheItemPoolInterface $cache;

    public function __construct(
        CacheItemPoolInterface $cache
    ) {
        $this->cache = $cache;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $this->cache->clear();

        $game->addInformation(_('Der PHP Cache Item Pool wurde geleert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
