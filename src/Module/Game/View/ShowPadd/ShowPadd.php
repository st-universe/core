<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowPadd;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowPadd implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PADD';

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $factionId = $game->getUser()->getFactionId();
        $templateFile = sprintf('html/tutorial/padd%d.twig', $factionId);
        $game->setTemplateFile($templateFile);
    }
}
