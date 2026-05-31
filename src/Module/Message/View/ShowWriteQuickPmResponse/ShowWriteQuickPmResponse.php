<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPmResponse;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowWriteQuickPmResponse implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_WRITE_QUICKPM_RESPONSE';

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/communication/writeQuickPmResponse.twig');
    }
}
