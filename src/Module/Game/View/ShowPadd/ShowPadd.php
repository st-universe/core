<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowPadd;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class ShowPadd implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PADD';

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $factionId = $game->getUser()->getFactionId();
        $templateFile = sprintf('html/tutorial/padd%d.twig', $factionId);
        $game->setTemplateFile($templateFile);
    }
}
