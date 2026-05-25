<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowBroadcastResponse;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowBroadcastResponse implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BROADCAST_RESPONSE';

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/ship/broadcastResponse.twig');
    }
}
