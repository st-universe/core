<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Psr\Cache\CacheItemPoolInterface;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class ResetCaches implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RESET_CACHES';

    public function __construct(private CacheItemPoolInterface $cache) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        $this->cache->clear();

        $game->getInfo()->addInformation(_('Der PHP Cache Item Pool wurde geleert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
